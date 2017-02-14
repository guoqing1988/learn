// 路径问题的多go程解法
// 用信息模拟行动，用channel模拟道路，用go程模拟路口，追求最快提供可行解和满意解
// 本算法还有一个使用sync/atomic的CompareAndSwap函数的版本，待更新
package main

import (
	"fmt"
	"time"
)

// 经过的路径的信息
type Info struct {
	Frm []int // 已经经过的节点
	Len int   // 已经行动的距离
}

// 表示一个不跨越节点的道路，即边
type Road struct {
	Len int       // 边的长度
	Dst chan Info // 边的目的地
}

// 代表一个节点，也就是道路的交叉点
type Node struct {
	StopIt chan bool // 用于传递关闭信息
	Import chan Info // 获取到达该节点的信息
	Export []Road    // 记录的所有从该节点出发的边
}

// 用于将一个矩阵表示的路径图转化为Node切片表示的路径图
func makeMap(num int, mtx [][]int) []Node {
	dots := make([]Node, num)
	for i := 0; i < num; i++ {
		dots[i].StopIt = make(chan bool, 1) // 为啥？看下文
		dots[i].Import = make(chan Info, 1) // 同上
	}
	for i := 0; i < num; i++ {
		for j := 0; j < num; j++ {
			if mtx[i][j] > 0 {
				dots[i].Export = append(dots[i].Export, Road{mtx[i][j], dots[j].Import})
			}
		}
	}
	return dots
}

// 计算方法
func calcWay(net []Node, frm, end int) (ans chan Info) {
	// 从起点出发的路径信息
	t := []int{frm}
	for _, ex := range net[frm].Export {
		ex.Dst <- Info{t, ex.Len}
	}
	ans = make(chan Info, 1) // 用于输出寻找到的路径
	for i, d := range net {
		// 起点和终点的处理和其他节点不同，不存在转发
		if i == frm || i == end {
			continue
		}
		go func(i int, n Node) {
			ex := n.Export
			s := 1 << 30 // 记录当前到达该节点的最短路程
			e := []int{} // 记录当前最短路程下的经历的节点顺序
			t := len(ex) // 用来标记向哪一个边转发信息
			for {
				select {
				case <-n.StopIt: // 接收到关闭信号就退出go程
					return
				case info := <-n.Import: // 尝试获取新到达该节点的信息
					if s > info.Len { // 更新当前最短路径信息
						s = info.Len
						e = append(info.Frm, i)
						t = 0
					}
				default:
					if t < len(ex) { // 如果t>=len(ex)表示已经转发完毕信息
						select {
						case ex[t].Dst <- Info{e, s + ex[t].Len}: // 信息的更新与转发
							t++ // 指向新的边
						default:
						}
					}
				}
			}
		}(i, d)
	}
	// 终点的处理，本函数实际上得到的并不能保证为最优解，但一般是满意解
	// 最大的好处是可以用最快的速度拿出一个可行解，并不断更新更好的解
	go func() {
		s := 1 << 30
		timer := time.NewTimer(time.Second * 20)
		for {
			select {
			case info := <-net[end].Import:
				timer.Reset(time.Second * 20)
				if s > info.Len {
					s = info.Len
					ans <- Info{append(info.Frm, end), info.Len}
				}
			case <-timer.C: // 20秒内都没有新的路径信息抵达，就认为得到结论了
				for _, d := range net {
					d.StopIt <- false
				}
				close(ans)
				return
			}
		}
	}()
	// 起点的处理，会丢弃接收到的全部信息
	// 其实起点可以和中转点的go程函数整合为一种，只不过没必要
	go func() {
		for {
			select {
			case <-net[frm].Import:
			case <-net[frm].StopIt:
				return
			}
		}
	}()
	return
}

// 这是一个路径图矩阵的例子
var mtx = [][]int{
	{0, 2, 0, 4, 0, 0, 0, 0, 0, 0},
	{2, 0, 3, 0, 2, 0, 0, 0, 0, 0},
	{0, 3, 0, 0, 0, 1, 0, 0, 3, 0},
	{4, 0, 0, 0, 3, 0, 3, 0, 0, 4},
	{0, 2, 0, 3, 0, 4, 2, 0, 0, 0},
	{0, 0, 1, 0, 4, 0, 3, 0, 2, 0},
	{0, 0, 0, 3, 2, 3, 0, 2, 0, 2},
	{0, 0, 0, 0, 0, 0, 2, 0, 2, 1},
	{0, 0, 3, 0, 0, 2, 0, 2, 0, 0},
	{0, 0, 0, 4, 0, 0, 2, 1, 0, 0}}

func main() {
	net := makeMap(10, mtx)
	ans := calcWay(net, 0, 8)

	// 不断获取最新的越来越好的解，并输出
	for info := range ans {
		fmt.Println(info)
	}
}
