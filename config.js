var environment = {
  development: { port: 8080, },
  production: { port: 80, }
}[process.env.NODE_ENV || 'production'];


exports.config = environment;
