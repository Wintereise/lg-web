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
        $slave = $this->validateSlave(Input::get('router'));
        if(!is_array($slave))
        {
            return Response::make('You tried to use a probe that does not exist', 400);
        }
        $target = $this->parseInput();
        if($target == false && $task != 'tipfile')
            return Redirect::route('index')
                ->with('results', 'Invalid input has been detected. If you chose a BGP command, please note that no prefixes longer than 24 bits (v4), or 48 bits (v6) are accepted.');
        $api = new lgAPI($slave['url'], $slave['key']);
        $ipType = $this->validateIP($target);
        switch($task)
        {
            case 'ping':
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
            break;

            case 'traceroute':
                if($ipType == 'v4')
                {
                    if (in_array('traceroute', $slave['cmds']))
                    {
                        $temp = $api->sendCommand('traceroute', $target)->getArrayResponse();
                        $streamID = isset($temp['data']) ? $temp['data'] : false;
                        if($streamID == false)
                            return Redirect::route('index')
                                ->with('results', 'We apologize, something went gravely wrong. Please contact support! (ERR_STREAM_GEN)')
                                ->withInput();
                        else
                        {
                            $response = $api->sendCommand('stream', $streamID)->getRawResponse();
                            if(isset($response['code']))
                                return Redirect::route('index')
                                    ->with('results', 'We apologize, something went gravely wrong. Please contact support! (ERR_STREAM_404)')
                                    ->withInput();
                            else
                                return Redirect::route('index')
                                    ->with('results', nl2br($response))
                                    ->withInput();
                        }
                    }
                    else
                        return Redirect::route('index')
                            ->with('results', 'We apologize, but that command (traceroute) is not enabled for this probe.');
                }
                else if ($ipType == 'v6')
                {
                    if (in_array('traceroute6', $slave['cmds']))
                    {
                        $temp = $api->sendCommand('traceroute6', $target)->getArrayResponse();
                        $streamID = isset($temp['data']) ? $temp['data'] : false;
                        if($streamID == false)
                            return Redirect::route('index')
                                ->with('results', 'We apologize, something went gravely wrong. Please contact support! (ERR_STREAM_GEN)')
                                ->withInput();
                        else
                        {
                            $response = $api->sendCommand('stream', $streamID)->getRawResponse();
                            if(isset($response['code']))
                                return Redirect::route('index')
                                    ->with('results', 'We apologize, something went gravely wrong. Please contact support! (ERR_STREAM_404)')
                                    ->withInput();
                            else
                                return Redirect::route('index')
                                    ->with('results', nl2br($response))
                                    ->withInput();
                        }
                    }
                    else
                        return Redirect::route('index')
                            ->with('results', 'We apologize, but that command (traceroute6) is not enabled for this probe.');
                }

            break;
            case 'bgp':
            break;

            case 'tipfile':
                if(in_array('tipfile', $slave['cmds']))
                {
                    $buffer = "";
                    if(isset($slave['test-ips'][0]))
                        $buffer .= "IPv4 Test IP: " . $slave['test-ips'][0] . PHP_EOL;
                    if(isset($slave['test-ips'][1]))
                        $buffer .= "IPv6 Test IP: " . $slave['test-ips'][1] . PHP_EOL;
                    if(isset($slave['test-files']))
                    {
                        $buffer .= "Test Files: ";
                        foreach ($slave['test-files'] as $file)
                        {
                            $buffer .= sprintf("<a href='%s'>%s </a>", $file['url'], $file['name']);
                        }
                        $buffer .= PHP_EOL;
                    }
                    else
                        $buffer .= "No test files are defined for this node, apologies." . PHP_EOL;
                    return Redirect::route('index')
                        ->with('results', nl2br($buffer))
                        ->withInput();
                }
                else
                    return Redirect::route('index')
                        ->with('results', 'We apologize, but that command (test files / ips) is not enabled for this probe.');
            break;
            default:
                return false;
        }
    }

    private function parseInput ()
    {
        $task = Input::get('query', false);
        $srcType = Input::get('sourceIP', false);
        switch ($task)
        {
            case 'ping':
            case 'traceroute':
                if($srcType != 'IP' && $srcType != 'FQDN' && $srcType != false)
                    $ip = $srcType;
                else if ($srcType == 'IP')
                    $ip = Input::get('addr');
                else
                {
                    //we got a fqdn
                    $preferredProtocol = Input::get('protocol', 'IPv4');
                    if($preferredProtocol == 'IPv4')
                        $ip = @dns_get_record(Input::get('addrFQDN'), DNS_A)[0]['ip'];
                    else
                        $ip = @dns_get_record(Input::get('addrFQDN'), DNS_AAAA)[0]['ipv6'];
                }
                if(strlen($ip) > 0 && $this->validateIP($ip) !== false)
                    return $ip;
                else
                    return false;
            break;
            case 'bgp':
                if($srcType == 'IP')
                {
                    $addr = Input::get('addr');
                    if(strpos($addr, '/') !== false)
                    {
                        //Most *likely* a CIDR
                        list($ip, $mask) = explode('/', $addr);
                        $type = $this->validateIP($ip);
                        if($type !== false)
                        {
                            switch ($type)
                            {
                                case 'v4':
                                    if ($mask > 0 && $mask <= 24)
                                        return $addr;
                                    else
                                        return false;
                                break;
                                case 'v6':
                                    if ($mask > 0 && $mask <= 48)
                                        return $addr;
                                    else
                                        return false;
                            }
                        }
                    }
                    else
                    {
                        //This is likely a single IP address instead.
                        if ($this->validateIP($addr) !== false)
                            return $addr;
                        else
                            return false;
                    }
                }
            break;
        }
        return false;
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

    private function validateSlave ($router)
    {
        if(isset(Config::get('slaves.slaves')[$router]))
            $slave = Config::get('slaves.slaves')[Input::get('router')];
        else
            $slave = null;
        return $slave;
    }

}