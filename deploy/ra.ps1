$whoami =
do {
ssh $whoami@in.test.vz.al -R "/home/$whoami/sockets/v4-3389:127.0.0.1:3389" -R "/home/$whoami/sockets/v4-22:127.0.0.1:22" -R "/home/$whoami/sockets/v4-5900:127.0.0.1:5900" -R "/tmp/sock/$whoami/v4-3389:127.0.0.1:3389" -R "/tmp/sock/$whoami/v4-22:127.0.0.1:22" -R "/tmp/sock/$whoami/v4-5900:127.0.0.1:5900" -o StreamLocalBindMask=0117 -o StreamLocalBindUnlink=yes -o UserKnownHostsFile=known_hosts -o CheckHostIP=no -o StrictHostKeyChecking=yes -i .ssh\id_rsa -o ServerAliveInterval=2 -o ServeraliveCountMax=3 -N -o ExitOnForwardFailure=yes
Start-Sleep 2
} until ($done)
