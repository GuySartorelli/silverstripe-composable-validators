# Resources

## JS Development
### Setup
For development you will need Node.js (via [nvm](https://github.com/nvm-sh/nvm)) and [yarn](https://yarnpkg.com/) installed.

Next, you need to install the required npm packages.
```shell
nvm use
yarn install
```
### Compiling assets
You can compile assets during development using:
```shell
nvm use
yarn watch
```

Produce minified (production) files using:
```shell
nvm use
yarn package
```

### Linting
```shell
nvm use
yarn lint
```
