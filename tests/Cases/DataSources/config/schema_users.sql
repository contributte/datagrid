CREATE TABLE users (
    id      INTEGER      PRIMARY KEY AUTOINCREMENT,
    name    VARCHAR (50),
    age     INTEGER (3),
    address VARCHAR (50),
    city    INTEGER (3) ,
    FOREIGN KEY(city) REFERENCES cities(id)
);
