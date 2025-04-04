pipeline {
    agent any

    environment {
        STAGING_IP = '54.196.165.194'
        PROD_IP = '18.208.127.21'
        DEPLOYMENT_IP = '54.196.165.194'
    }

    stages {
        stage('Clone Repo') {
            steps {
                git url: 'https://github.com/wbrymo/TEAM-22.git', branch: 'main'
            }
        }

        stage('Install Dependencies') {
            steps {
                sh '''
                    if [ -f /etc/redhat-release ]; then
                        sudo yum install -y epel-release || echo "epel-release not found, continuing..."
                        sudo yum install -y httpd mariadb-server php php-mysqlnd
                        sudo systemctl enable --now httpd
                        sudo systemctl enable --now mariadb
                    fi
                '''
            }
        }

        stage('Deploy PHP App') {
            steps {
                sh '''
                    sudo cp index.php /var/www/html/
                    sudo chown apache:apache /var/www/html/index.php
                    sudo chmod 644 /var/www/html/index.php
                '''
            }
        }

        stage('Import Database (Local)') {
            steps {
                sh '''
                    sudo mysql -u root -ppassword < init.sql
                '''
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

        // ✅ UPDATED STAGING DEPLOYMENT
        stage('Deploy to Staging') {
            steps {
                sshagent(['ubuntu']) {
                    sh '''
                        scp index.php init.sql ubuntu@54.196.165.194:~
                        ssh ubuntu@54.196.165.194 "sudo mv ~/index.php ~/init.sql /var/www/html/"
                        ssh ubuntu@54.196.165.194 "sudo mysql -u devops -pBuraimoh7 < /var/www/html/init.sql"
                        ssh ubuntu@54.196.165.194 "sudo systemctl restart apache2"
                    '''
                }
            }
        }

        stage('Manual Approval') {
            steps {
                input message: 'Promote to Production?', ok: 'Deploy'
            }
        }

        stage('Deploy to Production') {
            steps {
                sshagent(['ssh-key-id']) {
                    sh """
                        scp index.php ubuntu@$PROD_IP:/var/www/html/
                        ssh ubuntu@$PROD_IP 'sudo systemctl restart apache2'
                    """
                }
            }
        }

        stage('Optional: Import DB via Jenkins Credentials') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'mysql-creds', usernameVariable: 'DB_USER', passwordVariable: 'DB_PASS')]) {
                    sh '''
                        mysql -u "$DB_USER" -p"$DB_PASS" studentdb < init.sql
                    '''
                }
            }
        }
    }

    post {
        success {
            echo '✅ PHP CRUD App deployed successfully to staging and/or production!'
            echo "Visit app at: http://$DEPLOYMENT_IP"
        }
        failure {
            echo '❌ Deployment failed. Please check Jenkins logs for details.'
        }
    }
}
