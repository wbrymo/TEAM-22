pipeline {
    agent any

    parameters {
        booleanParam(name: 'PROMOTE_TO_PRODUCTION', defaultValue: false, description: 'Promote to production?')
        string(name: 'ARTIFACT_VERSION', defaultValue: 'latest', description: 'Enter version to deploy (e.g., 12 or "latest")')
    }

    environment {
        STAGING_IP = '54.221.67.162'
        PROD_IP = '34.239.133.252'
        DEPLOYMENT_IP = '54.221.67.162'
        NEXUS_URL = 'http://54.242.144.3:8081/repository/team22-artifacts'
    }

    stages {
        stage('Clone Repo') {
            steps {
                git url: 'https://github.com/wbrymo/TEAM-22.git', branch: 'main'
            }
        }

        stage('SonarQube Analysis') {
            steps {
                withCredentials([string(credentialsId: 'sonarqube-token', variable: 'SONAR_TOKEN')]) {
                    sh '''
                        /opt/sonar-scanner/bin/sonar-scanner \
                          -Dsonar.projectKey=TEAM-22 \
                          -Dsonar.sources=. \
                          -Dsonar.host.url=http://54.221.67.162:9000 \
                          -Dsonar.login=$SONAR_TOKEN
                    '''
                }
            }
        }

        stage('Install Dependencies') {
            steps {
                sh '''
                    if [ -f /etc/redhat-release ]; then
                        sudo yum install -y epel-release || echo "epel-release not found, continuing..."
                        sudo yum install -y httpd mariadb-server php php-mysqlnd zip unzip
                        sudo systemctl enable --now httpd
                        sudo systemctl enable --now mariadb
                    fi
                '''
            }
        }

        stage('Package Artifact') {
            steps {
                sh '''
                    mkdir -p artifacts
                    cp index.php init.sql artifacts/
                    zip -r team22-artifact-${BUILD_NUMBER}.zip artifacts
                '''
            }
        }

        stage('Upload Artifact to Nexus') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'nexus-creds', usernameVariable: 'NEXUS_USER', passwordVariable: 'NEXUS_PASS')]) {
                    sh '''
                        curl -v -u $NEXUS_USER:$NEXUS_PASS \
                          --upload-file team22-artifact-${BUILD_NUMBER}.zip \
                          $NEXUS_URL/team22-artifact-${BUILD_NUMBER}.zip
                    '''
                }
            }
        }

        stage('Download Artifact from Nexus') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'nexus-creds', usernameVariable: 'NEXUS_USER', passwordVariable: 'NEXUS_PASS')]) {
                    script {
                        def version = (params.ARTIFACT_VERSION == 'latest') ? "${BUILD_NUMBER}" : params.ARTIFACT_VERSION
                        def fileName = "team22-artifact-${version}.zip"
                        sh """
                            curl -u $NEXUS_USER:$NEXUS_PASS -O $NEXUS_URL/${fileName}
                            unzip -o ${fileName}
                        """
                    }
                }
            }
        }

        stage('Deploy PHP App') {
            steps {
                sh '''
                    sudo cp artifacts/index.php /var/www/html/
                    sudo chown apache:apache /var/www/html/index.php
                    sudo chmod 644 /var/www/html/index.php
                '''
            }
        }

        stage('Import Database (Local)') {
            steps {
                sh 'sudo mysql -u root -ppassword < artifacts/init.sql'
            }
        }

        stage('Restart Apache') {
            steps {
                sh 'sudo systemctl restart httpd'
            }
        }

        stage('Test Deployed App') {
            steps {
                sh "curl -I http://$DEPLOYMENT_IP"
            }
        }

        stage('Deploy to Staging') {
            steps {
                sshagent(['ubuntu']) {
                    sh '''
                        ssh-keyscan -H 54.221.67.162 >> ~/.ssh/known_hosts
                        scp artifacts/index.php artifacts/init.sql ubuntu@54.221.67.162:~
                        ssh ubuntu@54.221.67.162 "sudo mv ~/index.php ~/init.sql /var/www/html/"
                        ssh ubuntu@54.221.67.162 "sudo mysql -u root -ppassword < /var/www/html/init.sql"
                        ssh ubuntu@54.221.67.162 "sudo systemctl restart apache2"
                    '''
                }
            }
        }

        stage('Deploy to Production') {
            when {
                expression { params.PROMOTE_TO_PRODUCTION }
            }
            steps {
                sshagent(['ubuntu']) {
                    sh '''
                        ssh-keyscan -H 34.239.133.252 >> ~/.ssh/known_hosts
                        scp artifacts/index.php artifacts/init.sql ubuntu@34.239.133.252:~
                        ssh ubuntu@34.239.133.252 "sudo mv ~/index.php ~/init.sql /var/www/html/"
                        ssh ubuntu@34.239.133.252 "sudo mysql -u root -ppassword < /var/www/html/init.sql"
                        ssh ubuntu@34.239.133.252 "sudo systemctl restart apache2"
                    '''
                }
            }
        }
    }

    post {
        success {
            echo '✅ PHP CRUD App deployed successfully to staging and/or production!'
            echo "🌐 Visit app at: http://$DEPLOYMENT_IP"
        }
        failure {
            echo '❌ Deployment failed. Please check Jenkins logs for details.'
        }
    }
}
