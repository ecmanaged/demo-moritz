FROM debian:jessie
MAINTAINER ackstorm@ackstorm.com

ENV DEBIAN_FRONTEND noninteractive
RUN apt-get -qq -y update && apt-get -qq -y upgrade
RUN apt-get install -qq -y curl apt-utils
RUN curl --insecure -sL https://deb.nodesource.com/setup_4.x | bash -
RUN apt-get install -qq -y nodejs
RUN apt-get clean
RUN mkdir /opt/demo-moritz
COPY demo-moritz /opt/demo-moritz/
WORKDIR /opt/demo-moritz
RUN npm install -g pm2
RUN npm install

EXPOSE 80
CMD ["pm2","start","server.js","-i","0","--no-daemon"]
