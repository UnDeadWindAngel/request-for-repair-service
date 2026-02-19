FROM node:20-alpine

WORKDIR /app

# Копируем файлы зависимостей
COPY frontend/package*.json ./

# Устанавливаем зависимости
RUN npm install

EXPOSE 5173
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]