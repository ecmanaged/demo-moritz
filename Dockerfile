FROM eu.gcr.io/moritz-demo-1138/base:latest
MAINTAINER ackstorm@ackstorm.com

ENV DEBIAN_FRONTEND noninteractive
RUN mkdir /opt/demo
COPY demo-moritz /opt/demo

EXPOSE 80
CMD service php5-fpm start && nginx
