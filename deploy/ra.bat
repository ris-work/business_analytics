:loop
ssh server-new-2024@in.test.vz.al -R 127.0.0.1:24389:127.0.0.1:3389 -R 127.0.0.1:21022:127.0.0.1:22 -R 127.0.0.1:26900:127.0.0.1:5900 -o UserKnownHostsFile=known_hosts -o CheckHostIP=no -o StrictHostKeyChecking=yes -i .ssh\id_rsa -o ServerAliveInterval=2 -o ServeraliveCountMax=3 -N
timeout 2
goto :loop
