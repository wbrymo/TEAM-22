pipeline {
    agent any

    environment {
        // Optional: define default Sonar token here if not set in Jenkins credentials
        // SONAR_TOKEN = credentials('squ_e69afcf7bc0e13fb0228920ac19fb77bb5800de5') // if using Jenkins credentials store
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

        stage('Import Database') {
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

        stage('SonarQube Analysis') {
            steps {
                withSonarQubeEnv('SonarQube') {
                    sh '''
                        sonar-scanner \
                          -Dsonar.projectKey=Sonar \
                          -Dsonar.sources=. \
                          -Dsonar.host.url=http://http://52.23.161.172:9000 \
                          -Dsonar.login=$SONAR_TOKEN
                    '''
                }
            }
        }

        stage('Test Deployed App') {
            steps {
                sh 'curl -I http://34.227.46.140'
            }
        }
    }

    post {
        success {
            echo 'PHP CRUD App successfully deployed and database initialized!'
            echo 'Visit your app at: http://34.224.100.106'
        }
        failure {
            echo 'Deployment failed. Check Jenkins logs.'
        }
    }
}
