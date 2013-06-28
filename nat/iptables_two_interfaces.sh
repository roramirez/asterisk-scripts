#!/bin/sh

#
# Rules IPTABLES for Asterisk behind NAT
#
# Example:
#
#  IP 192.168.1.100
#  ( Asterisk LAN )  LAN  <-->  eth1  ( Firewall ) eth0  <-- Inet
#
#
#
#  Config asterisk LAN 
# 
#  /etc/asterisk/sip.conf   
#    [general]
#     externip = x.x.x.x(ip wan firewall)
#     localnet = 192.168.1.0/255.255.255.0 
#
#    [peer]
#      reinvite    = no 
#      canreinvite = no 
#      nat = yes
#  
#  /etc/asterisk/rtp.conf   
#    rtpstart=10000
#     rtpend=20000
#       
#  
#
iptables -F
iptables -X
iptables -t nat -F
iptables -t nat -X
iptables -t mangle -F
iptables -t mangle -X
iptables -P INPUT ACCEPT
iptables -P FORWARD ACCEPT
iptables -P OUTPUT ACCEPT

echo "1">/proc/sys/net/ipv4/ip_forward



#ASTERISK
ASTERISK_IP=192.168.1.100
WAN=eth0
DMZ=eth1
# RTP ports
iptables -t nat -A PREROUTING -i $WAN -m udp -p udp --dport 10000:20000 -j DNAT --to-destination $ASTERISK_IP
iptables -A FORWARD -i $WAN -o $DMZ -m udp -p udp --dport 10000:20000 -d $ASTERISK_IP -j ACCEPT

# SIP port
iptables -t nat -A PREROUTING -i $WAN -m udp -p udp --dport 5060 -j DNAT --to-destination $ASTERISK_IP
iptables -A FORWARD -i $WAN -o $DMZ -m udp -p udp --dport 5060 -d $ASTERISK_IP -j ACCEPT
~               
