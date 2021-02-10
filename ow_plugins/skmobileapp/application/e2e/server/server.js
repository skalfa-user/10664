var express = require('express');
var bodyParser = require('body-parser');
var routes = require('./routes.js');
var app = express();

app.use(express.static(__dirname + '/static'));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.enable('strict routing');

routes(app);

var server = app.listen(3004, '0.0.0.0', function () {
    console.log('app running on port.', server.address().port);
});
