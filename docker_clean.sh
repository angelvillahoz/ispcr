docker stop $(docker ps --filter "name=ispcr_ispcr_server_1" \
    --filter "name=ispcr_mariadb_server_1" \
    --filter "name=ispcr_php_server_1" \
    -q)
docker rm ispcr_ispcr_server_1 \
    ispcr_mariadb_server_1 \
    ispcr_php_server_1
docker rmi ispcr_ispcr_server \
    ispcr_php_server \
    $(docker images -f "dangling=true" -q)
docker volume prune -f
