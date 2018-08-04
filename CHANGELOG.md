### 0.1.1: August 4, 2018
* Initial release
* Production configurations ready
    - Can provision by `ansible-playbook server.yml -e env=production`
    - Can reprovision to fix ssl if not yet propagated by `ansible-playbook server.yml -e env=production --tags letsencrypt`
    - Can deploy to production by `ansible-playbook deploy.yml -e "site=jcos.io env=production"`
* Local development ready
    - Start local by running `vagrant up`