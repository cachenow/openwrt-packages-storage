module("luci.controller.acc", package.seeall)

function index()
  require("luci.i18n")
  entry({ "admin", "services", "acc" }, alias("admin", "services", "acc", "service"), translate("Leigod Acc"), 50)
  entry({ "admin", "services", "acc", "service" }, cbi("leigod/service"), translate("Leigod Service"), 30).i18n = "acc"
  entry({ "admin", "services", "acc", "device" }, cbi("leigod/device"), translate("Leigod Device"), 50).i18n = "acc"
  entry({ "admin", "services", "acc", "notice" }, cbi("leigod/notice"), translate("Leigod Notice"), 80).i18n = "acc"
  entry({ "admin", "services", "acc", "status" }, call("get_acc_status")).leaf = true
  entry({ "admin", "services", "acc", "start_acc_service" }, call("start_acc_service"))
  entry({ "admin", "services", "acc", "stop_acc_service" }, call("stop_acc_service"))
end

-- get_acc_status get acc status
function get_acc_status()
  -- util module
  local util      = require "luci.util"
  local uci       = require "luci.model.uci".cursor()
  local translate = luci.i18n.translate
  -- init result
  local resp      = {}
  -- init state
  resp.service    = translate("Acc Service Disabled")
  resp.state      = {}
  -- check if exist
  local exist     = util.exec("ps | grep acc-gw | grep -v grep")
  -- check if program is running
  if exist ~= "" then
    resp.service = translate("Acc Service Enabled")
  end
  -- get uci
  local results = uci:get_all("accelerator")
  for _, typ in pairs({ "Phone", "PC", "Game", "Unknown" }) do
    local state = uci:get("accelerator", typ, "state")
    -- check state
    local state_text = "None"
    if state == nil or state == '0' then
    elseif state == '1' then
      state_text = translate("Acc Catalog Started")
    elseif state == '2' then
      state_text = translate("Acc Catalog Stopped")
    elseif state == '3' then
      state_text = translate("Acc Catalog Paused")
    end
    -- store text
    resp.state[translate(typ .. "_Catalog")] = state_text
  end
  luci.http.prepare_content("application/json")
  luci.http.write_json(resp)
end

-- start_acc_service
function start_acc_service()
  -- util module
  local util      = require "luci.util"
  util.exec("/etc/init.d/acc enable")
  util.exec("/etc/init.d/acc restart")
  local resp = {}
  resp.result = "OK"
  luci.http.prepare_content("application/json")
  luci.http.write_json(resp)  
end

-- start_acc_service
function stop_acc_service()
  -- util module
  local util      = require "luci.util"
  util.exec("/etc/init.d/acc stop")
  util.exec("/etc/init.d/acc disable")
  local resp = {}
  resp.result = "OK"
  luci.http.prepare_content("application/json")
  luci.http.write_json(resp)  
end