FROM php:8.2-cli

# Outils de base utiles en dev (rien de spécifique au projet)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer (officiel, stable)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Dossier de travail utilisé par Codespaces
WORKDIR /workspace
