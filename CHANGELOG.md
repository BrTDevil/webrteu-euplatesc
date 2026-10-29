# Changelog

## [Unreleased]

### Adăugat
- `EuPlatescGateway`: plată pe pagina găzduită (formular POST cu `fp_hash` HMAC-MD5), Silent URL verificat, verificarea paginii de return.
- Semnătura prin `LengthPrefixedHmac` din `webrteu/payments`; descrierea comenzii trimisă fără diacritice.
