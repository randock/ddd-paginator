FROM nginx:latest

RUN rm -rf /var/cache/apk/*

COPY sf3.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /app/var/log/nginx \
    && touch /app/var/log/nginx/access.log && ln -sf /dev/stdout /app/var/log/nginx/access.log \
    && touch /app/var/log/nginx/error.log  && ln -sf /dev/stdout /app/var/log/nginx/error.log
