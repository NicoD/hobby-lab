FROM node:26-alpine

RUN apk add --no-cache openssl python3 make g++

WORKDIR /app

COPY package*.json ./
COPY prisma ./prisma

RUN npm ci --ignore-scripts \
    && ./node_modules/.bin/prisma generate \
    && npm rebuild argon2

EXPOSE 4000

CMD ["npm", "run", "start:dev"]
