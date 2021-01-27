sudo yum -y install libpng-devel glibc-devel
rm /tmp/isPcr
mkdir /tmp/isPcr
unzip -q ./assets/isPcr.zip -d /tmp/isPcr/
sudo mkdir -p /root/bin/x86_64
(cd /tmp/isPcr/isPcrSrc && sudo MACHTYPE=x86_64 make HG_WARN="")
sudo mv /root/bin/x86_64/isPcr /usr/local/bin/
