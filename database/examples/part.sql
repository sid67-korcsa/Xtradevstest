CREATE TABLE napok (
    id INT NOT NULL,
    nev VARCHAR(255) NOT NULL,
    leiras VARCHAR(255) NULL)
PARTITION BY LIST COLUMNS(nev)
    (
        PARTITION part01 VALUES IN ('Hetfo', 'Kedd'),
        PARTITION part02 VALUES IN ('Szerda', 'Csutortok'),
        PARTITION part03 VALUES IN ('Pentek', 'Szombat', 'Vasarnap')
    );

SELECT * FROM napok PARTITION (part01);
