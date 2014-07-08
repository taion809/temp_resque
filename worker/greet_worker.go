package main

import (
	"fmt"
	"github.com/benmanns/goworker"
	"log"
	"net/http"
	"net/url"
)

func init() {
	goworker.Register("Greet", greetWorker)
}

func greetWorker(queue string, args ...interface{}) error {
	log.Printf("Processing Queue: %s Values: %v ", queue, args)
	arg_map, ok := args[0].(map[string]interface{})
	if !ok {
		log.Println("Error, cannot cast to map")
		return nil
	}

	id := arg_map["id"]
	log.Println("Using ID: ", id)

	name := arg_map["name"].(string)
	log.Println("Using Name: ", name)

	endpoint := fmt.Sprintf("http://localhost/update/%s", id)

	log.Println("Using endpoint: ", endpoint)

	resp, err := http.PostForm(endpoint, url.Values{"name": {name}})
	if err != nil {
		panic(err)
	}

	log.Printf("Returned: %v", resp)

	return nil
}
