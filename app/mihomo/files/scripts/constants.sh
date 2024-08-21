#!/bin/sh

# permission
MIHOMO_USER="root"
MIHOMO_GROUP="mihomo"

# routing
FW_TABLE="mihomo"
FW_MARK="0x80"
FW_MARK_MASK="0xFF"
RULE_PREF="1024"
ROUTE_TABLE="80"
TUN_DEVICE="tun"

# paths
PROG="/usr/bin/mihomo"
HOME_DIR="/etc/mihomo"
PROFILES_DIR="$HOME_DIR/profiles"
MIXIN_FILE_PATH="$HOME_DIR/mixin.yaml"
RUN_DIR="$HOME_DIR/run"
RUN_APP_LOG_PATH="$RUN_DIR/app.log"
RUN_CORE_LOG_PATH="$RUN_DIR/core.log"
RUN_PROFILE_PATH="$RUN_DIR/config.yaml"
RUN_UI_DIR="$RUN_DIR/ui"

# scripts
SH_DIR="$HOME_DIR/scripts"
TUN_SH="$SH_DIR/tun.sh"

# nftables
NFT_DIR="$HOME_DIR/nftables"
HIJACK_NFT="$NFT_DIR/hijack.nft"
GEOIP_CN_NFT="$NFT_DIR/geoip_cn.nft"
GEOIP6_CN_NFT="$NFT_DIR/geoip6_cn.nft"
