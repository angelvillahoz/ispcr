FROM golang:1.16

WORKDIR /go/src/
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libkrb5-dev \
        libpng-dev \
        patch \
        unzip && \
    go get -u github.com/gorilla/mux

ADD . .
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -a ./cmd/main.go

# Build the isPCR commandline tool as a static binary so it can be run within
# Alpine without worrying about dependencies. Credit goes to Alex Reynolds
# (https://www.biostars.org/p/261839/#261983) for the patch.

RUN mkdir /tmp/isPcr && \
    unzip -q ./assets/isPcr.zip -d /tmp/isPcr/ && \
    cp /tmp/isPcr/isPcrSrc/isPcr/isPcr/makefile /tmp/isPcr/isPcrSrc/isPcr/isPcr/makefile.original && \
    patch /tmp/isPcr/isPcrSrc/isPcr/isPcr/makefile.original -i ./assets/static-makefile.patch -o /tmp/isPcr/isPcrSrc/isPcr/isPcr/makefile && \
    mkdir -p /root/bin/x86_64 && \
    (cd /tmp/isPcr/isPcrSrc && MACHTYPE=x86_64 make HG_WARN="") && \
    mv /root/bin/x86_64/isPcr ./

FROM alpine:3.13

COPY --from=0 /go/src/main ./cmd/ispcrserver
COPY --from=0 /go/src/isPcr /usr/local/bin
EXPOSE 8080
VOLUME ["/assets"]

WORKDIR /cmd
CMD ["./ispcrserver"]
