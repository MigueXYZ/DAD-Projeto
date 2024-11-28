
GROUP := dad-group-5
VERSION := 1.0.8


kubectl-pods:
	kubectl get pods

kubectl-apply:
	kubectl apply -f deployment

kubectl-update:
	kubectl delete -f deployment
	kubectl apply -f deployment


laravel-build:
	docker build -t registry.172.22.21.107.sslip.io/${GROUP}/api:v${VERSION} \
	-f ./deployment/DockerfileLaravel ./laravel \
	--build-arg GROUP=${GROUP}

laravel-push:
	docker push registry.172.22.21.107.sslip.io/${GROUP}/api:v${VERSION}

vue-build:
	docker build -t registry.172.22.21.107.sslip.io/${GROUP}/web:v${VERSION} -f ./deployment/DockerfileVue ./vue

vue-push:
	docker push registry.172.22.21.107.sslip.io/${GROUP}/web:v${VERSION}

ws-build:
	docker build -t registry.172.22.21.107.sslip.io/${GROUP}/ws:v${VERSION} -f ./deployment/DockerfileWS ./websockets

ws-push:
	docker push registry.172.22.21.107.sslip.io/${GROUP}/ws:v${VERSION}

tudo:
	docker build -t registry.172.22.21.107.sslip.io/${GROUP}/api:v${VERSION} \
	-f ./deployment/DockerfileLaravel ./laravel \
	--build-arg GROUP=${GROUP}
	docker push registry.172.22.21.107.sslip.io/${GROUP}/api:v${VERSION}
	docker build -t registry.172.22.21.107.sslip.io/${GROUP}/web:v${VERSION} -f ./deployment/DockerfileVue ./vue
	docker push registry.172.22.21.107.sslip.io/${GROUP}/web:v${VERSION}
	docker build -t registry.172.22.21.107.sslip.io/${GROUP}/ws:v${VERSION} -f ./deployment/DockerfileWS ./websockets
	docker push registry.172.22.21.107.sslip.io/${GROUP}/ws:v${VERSION}
	kubectl apply -f deployment
