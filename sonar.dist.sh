#!/bin/bash

/opt/sonarqube/sonar-scanner/bin/sonar-scanner \
  -Dsonar.projectKey=proaktiv \
  -Dsonar.sources=src \
  -Dsonar.host.url=http://localhost:9000 \
  -Dsonar.login=key \
  -Dsonar.scm.disabled=True \
  -Dsonar.sourceEncoding=UTF-8 \
  -Dsonar.tests=tests \
  -Dsonar.php.tests.reportPath=tests/_reports/phpunit.xml \
  -Dsonar.php.coverage.reportPaths=tests/_reports/phpunit.coverage.xml