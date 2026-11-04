# Changelog

## v1.0.0 - 2026-11-04

- Prima versiune stabilă (1.0.0). Fără schimbări de API față de 0.1.x.
- Descriere și keywords unificate pentru Packagist; homepage corectat.
- Necesită `webrteu/payments` ^1.0.

## v0.1.x

### Adăugat
- `EuPlatescGateway`: plată pe pagina găzduită (formular POST cu `fp_hash` HMAC-MD5), Silent URL verificat, verificarea paginii de return.
- Semnătura prin `LengthPrefixedHmac` din `webrteu/payments`; descrierea comenzii trimisă fără diacritice.
