<?php
/**
 * Created by PhpStorm.
 * User: Tomoyo
 * Date: 1/29/2015
 * Time: 午後 09:36
 */

class requestHandler extends BaseController
{
    public function showIndex ()
    {
        return View::make('default.index');
    }

    public function parseProcess ()
    {
        $task = Input::get('query', false);
        $response = false;
        switch($task)
        {
            case 'ping':
                $target = $this->parseInput();
                if($ipType = $this->validateIP($target))
                {
                    if(isset(Config::get('slaves.slaves')[Input::get('router')]))
                        $slave = Config::get('slaves.slaves')[Input::get('router')];
                    else
                        $slave = null;
                    if(!is_array($slave))
                    {
                        return Response::make('You tried to use a probe that does not exist', 400);
                    }
                    else
                    {
                        $api = new lgAPI($slave['url'], $slave['key']);
                        if($ipType == 'v4')
                        {
                            if(in_array('ping', $slave['cmds']))
                            {
                                $response = $api->sendCommand('ping', $target)->getArrayResponse();
                                $data = isset($response['data']) ? $response['data'] : $response['message'];
                                return Redirect::route('index')
                                    ->with('results', nl2br($data))
                                    ->withInput();
                            }
                            else
                                return Redirect::route('index')
                                    ->with('results', 'We apologize, but that command is not enabled for this probe.');
                        }
                        elseif ($ipType == 'v6')
                        {
                            if(in_array('ping6', $slave['cmds']))
                            {
                                $response = $api->sendCommand('ping6', $target)->getArrayResponse();
                                $data = isset($response['data']) ? $response['data'] : $response['message'];
                                return Redirect::route('index')
                                    ->with('results', nl2br($data))
                                    ->withInput();
                            }
                            else
                                return Redirect::route('index')
                                    ->with('results', 'We apologize, but that command (ping6) is not enabled for this probe.');
                        }
                        else
                        {
                            return Redirect::route('index')
                                ->with('results','We apologize, but you specified an invalid target to ping.' );
                        }
                    }
                }

            break;
            case 'traceroute':
            break;
            case 'bgp':
            break;
            default:
                return false;
        }
    }

    private function parseInput ()
    {
        $srcType = Input::get('sourceIP', false);
        if($srcType != 'IP' && $srcType != 'FQDN' && $srcType != false)
            $ip = $srcType;
        else if ($srcType == 'IP')
            $ip = Input::get('addr');
        else
        {
            //we got a fqdn
            $preferredProtocol = Input::get('protocol', 'IPv4');
            if($preferredProtocol == 'IPv4')
                $ip = dns_get_record(Input::get('addrFQDN'), DNS_A)[0]['ip'];
            else
                $ip = dns_get_record(Input::get('addrFQDN'), DNS_AAAA)[0]['ipv6'];
        }
        return $ip;
    }

    private function validateIP ($ip)
    {
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
            return 'v4';
        else if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
            return 'v6';
        else
            return false;
    }

}