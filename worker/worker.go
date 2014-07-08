package main

import (
	"fmt"
	"github.com/benmanns/goworker"
)

func main() {
	fmt.Println("Starting Worker!")
	if err := goworker.Work(); err != nil {
		panic(err)
	}
}
