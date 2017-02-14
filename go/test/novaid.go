package main

import (
	"flag"
	"fmt"
	"git.wdwd.com/nova/n_utils"
)

func init() {
	flag.Parse()
}
func main() {

	// fmt.Println(flag.NArg(), flag.Args())
	if flag.NArg() != 2 {
		fmt.Printf("usage:\n\tnovaid [en|de] string")
	}
	switch flag.Arg(0) {
	case "de":
		fmt.Println(n_utils.FakeIdDecode(flag.Arg(1)))
	case "en":
		fmt.Println(n_utils.FakeIdEncode(n_utils.Be_int(flag.Arg(1))))
	default:
		fmt.Printf("usage:\n\tnovaid [en|de] string")
	}
}
