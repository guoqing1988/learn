package main

import (
	"flag"
	"fmt"
	"golang.org/x/net/html"
	"io"
	"net/http"
	"net/url"
	"os"
	"path"
	"runtime"
	"strings"
)

type Store struct {
	test map[string]struct{}
	list []struct {
		string
		byte
	}
}

var (
	links Store
	image Store
	pages Store
	depth = 1
)

func (this *Store) Add(addr string, level byte) bool {
	_, ok := this.test[addr]
	if !ok {
		this.test[addr] = struct{}{}
		this.list = append(this.list, struct {
			string
			byte
		}{addr, level})
	}
	return !ok
}

func (this *Store) Has(addr string) bool {
	_, ok := this.test[addr]
	return ok
}

func (this *Store) Pop() (string, byte, bool) {
	if len(this.list) == 0 {
		return "", 0, false
	}
	x := this.list[0]
	this.list = this.list[1:]
	return x.string, x.byte, true
}

func panicError(err error) {
	if err == nil {
		return
	}
	_, file, line, ok := runtime.Caller(1)
	file = file[strings.LastIndexAny("/"+file, `\/`):]
	if ok {
		panic(fmt.Errorf("[%s/%s]%v", file, line, err))
	} else {
		panic(fmt.Errorf("[ERROR]%v", err))
	}
}

func encode(s string) string {
	if s == "/" {
		return "index.html"
	} else {
		s = s[1:]
	}
	if n := len(s) - 1; s[n] == '/' {
		s = s[:n] + ".html"
	}
	return strings.Replace(s, "/", "_", -1)
}

func fetch(addr string, level byte) {
	defer func() {
		err := recover()
		if err != nil {
			fmt.Println(err)
		}
	}()
	var walk func(n *html.Node)
	env, err := url.Parse(addr)
	env.Fragment = ""
	panicError(err)
	walk = func(n *html.Node) {
		var (
			href string
			sp   int
		)
		if n.Type == html.ElementNode {
			switch n.Data {
			case "a":
				sp = -1
				for i, attr := range n.Attr {
					if attr.Key == "href" {
						href = attr.Val
						sp = i
					}
				}
				if sp < 0 {
					break
				}
				pnt, err := env.Parse(href)
				panicError(err)
				rawquery := pnt.RawQuery
				pnt.RawQuery = ""
				fragment := pnt.Fragment
				pnt.Fragment = ""
				href = pnt.String()
				switch {
				case path.Ext(href) == ".php":
					if rawquery != "" {
						href += "?" + rawquery
					}
					if fragment != "" {
						href += "#" + fragment
					}
					n.Attr[sp].Val = href
				case int(level) < depth && pnt.Host == env.Host:
					pages.Add(href, level+1)
					fallthrough
				case pages.Has(href):
					href = encode(pnt.Path)
					if rawquery != "" {
						href += "?" + rawquery
					}
					if fragment != "" {
						href += "#" + fragment
					}
					if level == 0 {
						n.Attr[sp].Val = "./pages/" + href
					} else {
						n.Attr[sp].Val = "../pages/" + href
					}
				default:
					if rawquery != "" {
						href += "?" + rawquery
					}
					if fragment != "" {
						href += "#" + fragment
					}
					n.Attr[sp].Val = href
				}
			case "img":
				for i, attr := range n.Attr {
					if attr.Key == "src" {
						href = attr.Val
						sp = i
					}
				}
				pnt, err := env.Parse(href)
				pnt.Fragment = ""
				panicError(err)
				href = pnt.String()
				image.Add(href, 0)
				if level == 0 {
					n.Attr[sp].Val = "./image/" + encode(pnt.Path)
				} else {
					n.Attr[sp].Val = "../image/" + encode(pnt.Path)
				}
			case "script":
				sp = -1
				for i, attr := range n.Attr {
					if attr.Key == "src" {
						href = attr.Val
						sp = i
					}
				}
				if sp < 0 {
					break
				}
				pnt, err := env.Parse(href)
				pnt.Fragment = ""
				panicError(err)
				href = pnt.String()
				links.Add(href, 0)
				if level == 0 {
					n.Attr[sp].Val = "./links/" + encode(pnt.Path)
				} else {
					n.Attr[sp].Val = "../links/" + encode(pnt.Path)
				}
			case "link":
				sp = -1
				rel := ""
				for i, attr := range n.Attr {
					if attr.Key == "href" {
						href = attr.Val
						sp = i
					}
					if attr.Key == "rel" {
						rel = attr.Val
					}
				}
				if sp < 0 {
					break
				}
				if rel != "stylesheet" && rel != "shortcut icon" {
					break
				}
				pnt, err := env.Parse(href)
				pnt.Fragment = ""
				panicError(err)
				href = pnt.String()
				links.Add(href, 0)
				if level == 0 {
					n.Attr[sp].Val = "./links/" + encode(pnt.Path)
				} else {
					n.Attr[sp].Val = "../links/" + encode(pnt.Path)
				}
			}
		}
		for c := n.FirstChild; c != nil; c = c.NextSibling {
			walk(c)
		}
	}

	resp, err := http.Get(addr)
	panicError(err)
	defer resp.Body.Close()

	node, err := html.Parse(resp.Body)
	panicError(err)
	walk(node)

	var file *os.File
	if level == 0 {
		file, err = os.Create(encode(env.Path))
	} else {
		file, err = os.Create("./pages/" + encode(env.Path))
	}
	panicError(err)
	defer file.Close()
	err = html.Render(file, node)
	panicError(err)
}

func load(dir, addr string) {
	defer func() {
		err := recover()
		if err != nil {
			fmt.Println(err)
		}
	}()
	env, err := url.Parse(addr)
	env.Fragment = ""
	panicError(err)
	resp, err := http.Get(addr)
	panicError(err)
	defer resp.Body.Close()
	file, err := os.Create(dir + "/" + encode(env.Path))
	panicError(err)
	defer file.Close()
	_, err = io.Copy(file, resp.Body)
	panicError(err)
}

func init() {
	links.test = map[string]struct{}{}
	image.test = map[string]struct{}{}
	pages.test = map[string]struct{}{}
	flag.IntVar(&depth, "d", 1, "depth to inside")
	flag.Parse()
}

func main() {
	if flag.NArg() != 1 {
		fmt.Println("usage:\n\texefile [-d=number] urlpath")
		flag.PrintDefaults()
		return
	}
	// os.Mkdir("fold", 0666)
	// os.Chdir("fold")
	os.Mkdir("pages", 0666)
	os.Mkdir("links", 0666)
	err := os.Mkdir("image", 0666)
	panicError(err)
	fetch(flag.Arg(0), 0)
	for {
		addr, level, ok := pages.Pop()
		if !ok {
			break
		} else {
			fmt.Println(addr)
		}
		fetch(addr, level)
	}
	for {
		addr, _, ok := links.Pop()
		if !ok {
			break
		} else {
			fmt.Println(addr)
		}
		load("links", addr)
	}
	for {
		addr, _, ok := image.Pop()
		if !ok {
			break
		} else {
			fmt.Println(addr)
		}
		load("image", addr)
	}
}
