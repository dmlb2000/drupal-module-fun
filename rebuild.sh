#!/bin/bash -xe

ADMIN_PASS=$(uuidgen)
drush -y si --site-name='Drupal Site' --db-url=mysql://drupal:drupal@localhost:3306/drupal --account-pass=${ADMIN_PASS}
drush -y en devel_php devel_generate facets search_api_solr jsonapi basic_auth key_auth
drush -y pmu search
drush -y en pacifica_devel
drush -y cset jsonapi.settings read_only 0
drush -y genu 20
for x_type in chairs tables desks shelves lights misc; do
  drupal pacifica_devel:create:content_type --prefix=office --name=${x_type}
  drush -y gent office_${x_type}_types 10
done
drupal pacifica_devel:create:display_config --prefix=office
drush gent office_category 10
drush gent office_relationships 10
drush cr
CURL_CMD='curl --user admin:'${ADMIN_PASS}
for x_type in chairs tables desks shelves lights misc; do
  $CURL_CMD -X POST -H 'Content-Type: application/vnd.api+json' http://drupalvmdev.test/jsonapi/node/office_${x_type} -d'{
  "data": {
    "type": "node--office_'${x_type}'",
    "attributes": {
      "title": "The first '${x_type}'"
    }
  }
}'
done
for x_type in chairs tables desks shelves lights misc; do
  drush -y genc --types=office_${x_type} 20
done
drupal pacifica_devel:create:search_config --prefix=office
drush search-api:reset-tracker
drush search-api:clear
drush search-api:status
drush uli | sed 's/default/drupalvmdev.test/'
