# Używaj oficjalnego obrazu Node.js jako podstawy
FROM node:20

# Ustaw katalog roboczy w kontenerze
WORKDIR /app

# Skopiuj plik package.json i package-lock.json (jeśli istnieje)
COPY package*.json ./

# Zainstaluj zależności (z cache dla npm install)
RUN npm install --silent

# Skopiuj pozostałe pliki projektu
COPY . .

# Eksponuj port 3000
EXPOSE 3000

# Uruchom aplikację
CMD ["npm", "start"]
