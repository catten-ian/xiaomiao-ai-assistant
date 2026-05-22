module.exports = {
  apps: [{
    name: 'freechat',
    script: './server.js',
    cwd: '/var/www/freechat',
    env: {
      NODE_ENV: 'production'
    }
  }]
}
