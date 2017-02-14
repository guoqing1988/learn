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
	chs = make(chan int, 20)
	ans = make(chan string)
)

// 每个线程的操作
func work(v string) {
	ans <- v
}

func main() {
	// 用于分发的线程
	go func() {
		for _, v := range urls {
			// 限制线程数
			chs <- 0
			wrg.Add(1)
			go func(v string) {
				defer func() {
					<-chs
					wrg.Done()
				}()
				work(v)
			}(v)
		}
		// 等待至所有分发出去的线程结束
		wrg.Wait()
		close(ans)
	}()
	// 收集各个线程返回的信息
	for each := range ans {
		println(`"` + each + `"`)
	}
}
