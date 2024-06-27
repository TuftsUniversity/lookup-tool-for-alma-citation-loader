#!/bin/bash

# Install dependencies
npm install

# Run the PHP server
php -S localhost:8000 &

# Open the HTML file in the default browser
xdg-open index.html
