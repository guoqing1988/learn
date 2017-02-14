package main

import (
	"fmt"
	"github.com/ugorji/go/codec"
	"time"
)

type UserStruct struct {
	ID   int32
	Name string
}
type ts struct {
	C   string
	K   string
	T   int
	Max int
	Cn  string
}

var (
	b  []byte
	mh codec.MsgpackHandle
)

func main() {

	var in = &ts{
		C:   "LOCK",
		K:   "及第三方来家里的时刻就付款了加肥加大索拉卡附近的顺口溜进付款鲁大师就发的是家乐福空间都说了发空间都上来看积分离开的设计费凉快圣诞节疯狂了坚实的路口南方，没耐心，没辞职了空间都，明星们快乐附件二佛，梦想能付款圣诞节离开福建省的李开复你是，门，没想你了肯德基法律框架",
		T:   1000,
		Max: 200,
		Cn:  "中文",
	}
	ExampleMsgpack(in)
	return
	user := UserStruct{9, "abcd"}
	//关键调用
	mh.StructToArray = true

	enc := codec.NewEncoderBytes(&b, &mh)
	err := enc.Encode(user)
	if err == nil {
		fmt.Println("data:", b)
	} else {
		fmt.Println("err:", err)
	}

	dec := codec.NewDecoderBytes(b, &mh)
	var new_user UserStruct
	err = dec.Decode(&new_user)
	if err == nil {
		fmt.Println("new_user:", new_user)
	} else {
		fmt.Println("err:", err)
	}

}
func ExampleMsgpack(in *ts) {

	t1 := time.Now()

	for i := 0; i < 100000; i++ {
		// encode
		mh.StructToArray = true

		enc := codec.NewEncoderBytes(&b, &mh)
		err := enc.Encode(in)
		if err == nil {
			fmt.Println("data:", b)
		} else {
			fmt.Println("err:", err)
		}

		dec := codec.NewDecoderBytes(b, &mh)
		var new_user ts
		err = dec.Decode(&new_user)
		if err == nil {
			fmt.Println("new_user:", new_user)
		} else {
			fmt.Println("err:", err)
		}
	}
	t2 := time.Now()
	fmt.Println("msgpack 消耗时间：", t2.Sub(t1), "秒")
}
