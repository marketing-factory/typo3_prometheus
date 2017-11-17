# Prometheus configuration

#### Table of Contents
1. [Description - What does this configuration and why it is useful](#description)
2. [Setup - The basics of getting started with prometheus](#setup)
    * [What prometheus affects](#what-prometheus-affects)
    * [Setup requirements](#setup-requirements)
    * [Configuring prometheus](#configuring-prometheus)
3. [Usage - Configuration options and additional functionality](#usage)
4. [Reference](#reference)
5. [Limitations](#limitations)


## Description

If you do not have a central Prometheus server, you can follow this guide, to have a local Prometheus setup on your
webserver for displaying metrics of your TYPO3 installation and the underlying server system. You can also use
this configuration to setup a central prometheus server for monitoring multiple TYPO3 instances. 

Inside this directory, you find all the needed configurations to run a local working Prometheus server, 
a Grafana dashboard, an exporter for your system metrics and a proxy with SSL and authentication. 
 
The setup should not take longer than a couple of minutes. All you need is an installed and running docker daemon 
and shell access on the target system. When you are already running your TYPO3 instance on a container orchestration 
like docker swarm or kubernetes, you'll better take the configuration from the docker-compose.yml file and fit it the
needs of your environment.
    
After setting all this up, you can integrate the dashboard seamlessly into your TYPO3 Backend with the Prometheus 
backend module.

## Setup

### What prometheus affects

When you run this setup to monitor one TYPO3 instance, the resource allocation should be low. We do not expect that
there will be much cpu, memory and disk space or i/o resources needed. When you run this setup to monitor multiple 
instances, the resource usage depends on the instance count - then you should carefully inspect the resource usage 
and possibly consider to run this on dedicated machine.

All services will use non-standard ports, so you won't have any conflicts with your webserver, database or any other
service which is exposing on a networking port. This Setup does not require root privileges, a user which has access
to the docker daemon is sufficient.

### Setup Requirements

You will need at least:

- docker daemon >= 1.13.0
- docker compose >= 1.10
- shell access 
- access to docker daemon

We'll recommend to use the latest stable software versions of docker and docker compose.

### Configuring Prometheus

Since you have to edit the configuration files for your prometheus server and for the docker swarm stack, we'll
recommend to copy this directory to a location outside of TYPO3. So you won't have any modified files inside the
extension.

For succesfully running the setup you will need to setup following configuration:
- Location of your SSL Certificate
- Location of a htpasswd file for securing the Dashboard Access
- Hostname to use for accessing the Metrics Dashboard
- Hostname of the TYPO3 installation (should be the same, except you are running this setup for multiple TYPO3 sites)

In the following guide i will assume that you copied the configuration to  `/opt/PrometheusDocker`, when you chose any
other path, just modify your configuration accordingly. 

First of all you should create a htpasswd file with the same name as the hostname for the dashboard access.  When you'll
use this as an installation for one TYPO3 instance, you should set this to the domain which is used to access your TYPO3
backend. In the following configuration, we will use `www.example.org` as our domain to access the TYPO3 Backend.

`~ mkdir /opt/PrometheusDocker/htpasswd`

`~ htpasswd -cb /opt/PrometheusDocker/htpasswd/www.example.org user password`

Then we will copy the SSL Certificates, which are already present on our server. it is important, that the names for our 
certificates match our hostname, so the proxy can automatically include the certificates (When you do not have any SSL
certificates installed, you should really get some now!):

`~ mkdir /opt/PrometheusDocker/ssl`

`~ cp ssl_certificate.www.example.de.cert /opt/PrometheusDocker/ssl/www.example.de.crt`

`~ cp ssl_certificate.www.example.de.key /opt/PrometheusDocker/ssl/www.example.de.key`


By editing the file `docker-compose.yml` we configure our proxy service, which is responsible for SSL termination and
password protected access to the metrics dashboard:

Setup the hostname which we will use for the dashboard access to `www.example.org`: 
```
grafana:
  .
  .
  environment:
    - 'VIRTUAL_HOST=www.example.org'
```

Setup the paths to your htpasswd and ssl certificates you'll setup above: 
```
proxy:
  .
  .
  volumes: 
    - /opt/PrometheusDocker/htpasswd:/etc/nginx/htpasswd
    - /opt/PrometheusDocker/ssl:/etc/nginx/certs
```

Then we'll setup the scrape config for prometheus, so we'll get some metrics from our TYPO3 installation, by editing 
`/opt/PrometheusDocker/prometheus/prometheus.yml`:

```
scrape_configs:
  - job_name: 'typo3'
    .
    .
    static_configs:
         - targets: [ 'www.example.org' ]
```

Now we can start our docker swarm stack by issuing the following commands on our commandline from inside our 
configuration directory `/opt/PrometheusDocker`:

`~ docker swarm init`

`~ docker stack deploy -c docker-compose.yml prometheus`


