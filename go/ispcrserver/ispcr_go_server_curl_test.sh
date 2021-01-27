curl -v \
     -F speciesShortName=dmel \
     -F genomeAssemblyReleaseVersion=dm6 \
     -F input=@./input.fa \
     -F maximumPcrProductSize=4000 \
     -F minimumGoodMatchesSize=15 \
     -F minimumPerfectMatchSize=15 \
     -F flipReversePrimer=false \
     -F outputFormat=fa \
     http://127.0.0.1:8080
