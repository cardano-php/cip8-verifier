# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2025-07-31

### Added

#### Core Features
- **CIP-8 Signature Verification** - Complete implementation of Cardano's CIP-8 message signing standard
- **Ed25519 Cryptographic Verification** - Secure signature validation using PHP's sodium extension
- **Multi-Wallet Support** - Compatible with both lite wallets (Nami, Eternl) and hardware wallet (Ledger)
- **Network Support** - Full support for both Cardano mainnet and testnet environments

#### Clean Architecture Implementation
- **SOLID Principles** - Modular design following Single Responsibility, Open/Closed, and Dependency Inversion principles
- **Dependency Injection** - Constructor-based dependency injection for all services
- **Service Layer Architecture** - Separated concerns across focused service classes:
  - `PublicKeyExtractor` - CBOR signature key parsing and public key extraction
  - `StakeAddressGenerator` - Cardano stake address generation from public keys  
  - `Bech32Encoder` - Bech32 address encoding with full checksum validation
  - `CoseParser` - COSE_Sign1 message structure parsing
  - `SignatureVerifier` - Ed25519 signature and payload verification

#### Type Safety & Modern PHP
- **PHP 8.1+ Support** - Full utilization of modern PHP features
- **Readonly Classes** - Immutable DTOs using readonly class declarations
- **Strong Type Hints** - Complete type coverage across all public and private APIs
- **Union Types** - Modern type declarations (e.g., `string|null`)

#### Data Transfer Objects (DTOs)
- **`VerificationRequest`** - Immutable input data structure with factory methods
- **`VerificationResult`** - Comprehensive output data with validation details
- **`CoseSign1`** - Structured representation of COSE_Sign1 data

#### Exception Handling
- **Custom Exception Hierarchy** - Specific exceptions for different error scenarios:
  - `CIP8VerificationException` - Base exception for all library errors
  - `InvalidSignatureLengthException` - Ed25519 signature length validation
  - `InvalidPublicKeyLengthException` - Ed25519 public key length validation

#### Utility Classes
- **`Blake2bHasher`** - Centralized Blake2b hashing operations for hardware wallet support
- **`CborHelper`** - CBOR encoding/decoding utilities with RFC 9052 compliance

#### API Design
- **Multiple API Patterns** - Support for both modern type-safe and legacy array-based APIs
- **Factory Methods** - Convenient object creation patterns
- **Fluent Interface** - Clean, readable method chaining where appropriate
- **Backward Compatibility** - Legacy `verifySignature()` method preserved for existing integrations

#### Developer Experience
- **Comprehensive Documentation** - Complete API reference with examples
- **Clear Error Messages** - Descriptive error reporting for debugging
- **Working Demo** - Functional [example code](demo.php) with real test data

#### Security Features
- **Input Validation** - Comprehensive validation of all cryptographic inputs
- **Length Verification** - Strict validation of Ed25519 key and signature lengths
- **Secure CBOR Parsing** - Protected parsing with proper error handling
- **No Data Leakage** - Secure memory handling for cryptographic operations

#### Performance Optimizations
- **Efficient CBOR Processing** - Optimized parsing and encoding algorithms
- **Minimal Memory Allocation** - Resource-efficient object creation
- **Native Cryptography** - Direct use of sodium extension for maximum performance
- **Service Caching** - Reusable service instances through dependency injection

#### Standards Compliance
- **RFC 9052 Compliance** - Proper COSE_Sign1 signature structure creation
- **CIP-8 Specification** - Full adherence to Cardano's signing standard
- **PSR Standards** - Following PHP-FIG recommendations for code quality
- **Semantic Versioning** - Proper version management for library consumers

### Security
- All cryptographic operations use the battle-tested `sodium` extension
- Input validation prevents malformed data attacks
- Secure CBOR parsing with comprehensive error handling
- No sensitive data exposure in error messages or logs

### Performance
- Zero-copy CBOR operations where possible
- Efficient bech32 encoding implementation
- Minimal object allocation strategy
- Fast Ed25519 signature verification

### Documentation
- Complete API reference documentation
- Architecture diagrams showing system design
- Multiple usage examples covering common scenarios
- Error handling patterns and best practices
- Security considerations and recommendations

### Testing
- Comprehensive test coverage for all verification scenarios
- Real-world test data from actual Cardano wallets
- Edge case validation and error condition testing
- Performance benchmarking for critical operations

## Release Notes

This initial release provides a complete, production-ready implementation of Cardano CIP-8 signature verification for PHP applications. The library has been architected with clean code principles, comprehensive error handling, and strong type safety to ensure reliable operation in production environments.

The modular design allows for easy testing, maintenance, and future enhancements while maintaining backward compatibility for existing integrations.

### Migration from Previous Versions
This is the initial release - no migration required.

### Known Limitations
- Currently supports Ed25519 signatures only (as per CIP-8 specification)
- Requires PHP 8.1+ for readonly class support
- Depends on `ext-sodium` for cryptographic operations

### Future Roadmap
- Enhanced debugging and logging capabilities
- Performance optimizations for high-throughput scenarios
- Additional wallet compatibility testing
- Extended documentation and tutorials

[1.0.0]: https://github.com/cardano-php/cip8-verifier/releases/tag/v1.0.0