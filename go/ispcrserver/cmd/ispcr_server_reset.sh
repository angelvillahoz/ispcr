#cd cmd && go build -a ./main.go
docker stop $(docker ps -a | grep "ispcr_server" | cut -d" " -f1)
docker rm $(docker ps -a | grep "ispcr_server" | cut -d" " -f1)
docker rmi $(docker images | grep "ispcr_server" | cut -d" " -f1)
docker-compose up -d ispcr_server
docker image prune --force
docker volume prune --force
