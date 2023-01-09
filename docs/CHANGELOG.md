## [1.1.2](https://github.com/nstwfdev/mysql-connection-pool/compare/v1.1.1...v1.1.2) (2023-01-09)


### Docs

* fix namespaces in examples ([8edb845](https://github.com/nstwfdev/mysql-connection-pool/commit/8edb8455764a492f1e6196859084ae2bcdbc448f))

### Fix

* add 'query' and 'transaction' methods to PoolInterface ([0bb4efe](https://github.com/nstwfdev/mysql-connection-pool/commit/0bb4efe1a165ec534665f50b585989c21fbeedf0))

## [1.1.1](https://github.com/nstwfdev/mysql-connection-pool/compare/v1.1.0...v1.1.1) (2022-12-23)


### Fix

* spl object storage rewind before detach ([16f241d](https://github.com/nstwfdev/mysql-connection-pool/commit/16f241dc59e48cad17d202c9cbb3f4cbb6fb06a3))

# [1.1.0](https://github.com/nstwfdev/mysql-connection-pool/compare/v1.0.0...v1.1.0) (2022-12-22)


### Docs

* add README.MD ([8140820](https://github.com/nstwfdev/mysql-connection-pool/commit/8140820a1c0d22bab5d8e83474833762c977e76b))
* fix getConnection example in readme ([f132cd6](https://github.com/nstwfdev/mysql-connection-pool/commit/f132cd617884b474ba358948841367d67583d2bb))

### New

* add new 'Pool::query()' and 'Pool::transaction()' shortcut methods ([6e2b73a](https://github.com/nstwfdev/mysql-connection-pool/commit/6e2b73ab30a7e190cdcbab84e9ed7112effd67d0))

### Upgrade

* nstwf/mysql-connection to 1.2.1 ([f4498aa](https://github.com/nstwfdev/mysql-connection-pool/commit/f4498aa86b75797781300ae084a6f0543147c34b))
* react/async to require-dev ([c46aa6c](https://github.com/nstwfdev/mysql-connection-pool/commit/c46aa6cee3a073820579225fb094cf2ae7d7e7ab))

# 1.0.0 (2022-12-22)


### Breaking

* Initial commit. Realize connection pool ([7bc9638](https://github.com/nstwfdev/mysql-connection-pool/commit/7bc9638c4c229f87ff3a079b4289228159cdbb9b))

### Build

* add dependabot config ([56fa226](https://github.com/nstwfdev/mysql-connection-pool/commit/56fa2265409be433526a4b3620e5f09a1bb66c6e))
* Add gitlab actions ([686afa9](https://github.com/nstwfdev/mysql-connection-pool/commit/686afa94c5d58930e489dbff2571947e4b90208d))

### Chore

* typo fix in PoolInterface ([c591637](https://github.com/nstwfdev/mysql-connection-pool/commit/c591637b9c97e8274e230631cc93d2f0a23367d3))

### Fix

* default connection limit = 5 ([71337db](https://github.com/nstwfdev/mysql-connection-pool/commit/71337db746d8d46ae84e22e6363289a65fb484a6))
