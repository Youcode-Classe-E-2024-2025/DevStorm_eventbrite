CREATE DATABASE eventbrite IF NOT EXISTS;

CREATE TABLE users IF NOT EXISTS(
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) CHECK (role IN ('participant', 'organisateur', 'admin')) NOT NULL,
    name VARCHAR(255),
    avatar VARCHAR(255),  -- lien vers l'image de l'avatar
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE events IF NOT EXISTS(
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    date TIMESTAMP NOT NULL,
    location VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    capacity INT NOT NULL,
    category_id INT REFERENCES categories(id),
    organizer_id INT REFERENCES users(id),
    status VARCHAR(50) CHECK (status IN ('en attente', 'actif', 'terminé')) DEFAULT 'en attente',
    image_url VARCHAR(255),
    video_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE event_images IF NOT EXISTS(
    id SERIAL PRIMARY KEY,
    event_id INT REFERENCES events(id),
    image_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE categories IF NOT EXISTS(
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL
);


CREATE TABLE tickets IF NOT EXISTS(
    id SERIAL PRIMARY KEY,
    event_id INT REFERENCES events(id),
    user_id INT REFERENCES users(id),
    ticket_type VARCHAR(50) CHECK (ticket_type IN ('gratuit', 'payant', 'VIP', 'early bird')) NOT NULL,
    status VARCHAR(50) CHECK (status IN ('réservé', 'annulé', 'validé')) DEFAULT 'réservé',
    price DECIMAL(10, 2),
    qr_code VARCHAR(255),  -- Lien vers l'image du QR code
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE payments IF NOT EXISTS(
    id SERIAL PRIMARY KEY,
    ticket_id INT REFERENCES tickets(id),
    payment_method VARCHAR(50) CHECK (payment_method IN ('stripe', 'paypal')) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_status VARCHAR(50) CHECK (payment_status IN ('en attente', 'réussi', 'échoué')) DEFAULT 'en attente',
    transaction_id VARCHAR(255) UNIQUE,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE promotions IF NOT EXISTS(
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_percentage INT CHECK (discount_percentage > 0 AND discount_percentage <= 100),
    valid_from TIMESTAMP,
    valid_until TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE notifications IF NOT EXISTS(
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id),
    message TEXT NOT NULL,
    notification_type VARCHAR(50) CHECK (notification_type IN ('email', 'alerte')) NOT NULL,
    status VARCHAR(50) CHECK (status IN ('non lue', 'lue')) DEFAULT 'non lue',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

