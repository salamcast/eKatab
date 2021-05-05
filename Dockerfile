FROM debian:buster-20210329-slim
RUN apt-get update && apt-get -y install php-zip php-xsl php-xml php-cli
COPY . APP
RUN bash /APP/install.sh
EXPOSE 8080
# generate start server script
RUN echo -e "#!/bin/bash\ncd /APP\nphp -S 0.0.0.0:8080 -d ." > /start.sh
CMD [ "bash", "/start.sh" ]