# notes

```
drush en devel_php devel_generate facets search_api_solr
drush pmu search
drush generate module-standard
drush en pacifica_devel
drupal generate:command
drush pmu pacifica_devel
drush en pacifica_devel
```

```
drupal -vvv form:submit --formclass='node_type' --entityclass='\Drupal\node\Entity\NodeType' --varsfile=$PWD/web/modules/contrib/pacifica_devel/new_content_type.yml --operation=add
```