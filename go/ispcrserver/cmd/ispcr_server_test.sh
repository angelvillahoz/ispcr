docker-compose exec ispcr_server /usr/local/bin/ispcr -q=dna -minIdentity=100 -out=blast9 /assets/dm6.2bit ./input.fa output.blast9
docker-compose exec ispcr_server cat ./output.blast9