# The QR code holds a random ID, not the database primary key

The back of every Camp ID carries only a QR code, with no printed number, and cards are printed on paper and laminated, so a card can't cheaply be reissued. The QR encodes a long random unique ID stored beside the Attendee's normal primary key, never the key itself, because sequential keys (1 to about 6,000) could be guessed or forged by printing a fake QR, which defeats the point of the gate. When a QR won't scan, Security finds the Attendee by searching first name within a Circuit on the scanner page.
