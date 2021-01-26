docker-compose exec ispcr_server /usr/local/bin/isPcr -maxSize=4000 -minPerfect=15 -minGood=15 -out=fa /assets/dm6.2bit query output
docker-compose exec ispcr_server cat ./output
