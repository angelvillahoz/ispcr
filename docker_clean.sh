docker stop $(docker ps -a -q)
docker rm $(docker ps -a -q)
docker rmi ispcr_ispcr_server
docker rmi ispcr_php_server
docker rmi $(docker images -f "dangling=true" -q)
docker volume prune -f
