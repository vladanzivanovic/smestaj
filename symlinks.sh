#!/usr/bin/env bash
php bin/console assets:install --symlink --relative
php bin/console assetic:dump
php bin/console fos:js-routing:dump