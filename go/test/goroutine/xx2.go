package main

import "sync"

var (
	urls = []string{
		"01", "02", "03", "04", "05", "06",
		"07", "08", "09", "10", "11", "12",
		"13", "14", "15", "16", "17", "18",
		"19", "20", "21", "22", "23", "24",
		"25", "26", "27", "28", "29", "30",
	}
	wrg = sync.WaitGroup{}
	par = make(chan string)
	ans = make(chan string)
)

// 每个线程的操作
func work(v string) {
	ans <- v
}

func main() {
	// 建立20个循环处理任务的go程
	wrg.Add(20)
	for i := 0; i < 20; i++ {
		go func() {
			defer func() {
				// go程退出时通知wrg
				wrg.Done()
			}()
			for {
				// 全部输入结束后才退出
				v, ok := <-par
				if !ok {
					return
				}
				work(v)
			}
		}()
	}
	// 本线程用于分发任务给20个go程
	go func() {
		for _, v := range urls {
			par <- v
		}
		// 关闭通道标志着输入分发完毕
		close(par)
		// 等待20个go程全部退出
		wrg.Wait()
		// 关闭通道标志着输出完毕
		close(ans)
	}()

	// 收集各个线程返回的信息
	for each := range ans {
		println(`"` + each + `"`)
	}
}
