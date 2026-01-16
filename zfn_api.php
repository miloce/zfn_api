<?php

class ZfnClient
{
    private string $baseUrl;
    private array $headers;
    private array $cookies;
    private int $timeout;
    private array $raspisanie;

    public function __construct(array $cookies, array $options = [])
    {
        $this->baseUrl = rtrim($options['base_url'] ?? '', '/') . '/';
        $this->timeout = $options['timeout'] ?? 3;
        $this->raspisanie = $options['raspisanie'] ?? [
            ['8:00', '8:40'],
            ['8:45', '9:25'],
            ['9:30', '10:10'],
            ['10:30', '11:10'],
            ['11:15', '11:55'],
            ['14:30', '15:10'],
            ['15:15', '15:55'],
            ['16:05', '16:45'],
            ['16:50', '17:30'],
            ['18:40', '19:20'],
            ['19:25', '20:05'],
            ['20:10', '20:50'],
            ['20:55', '21:35'],
        ];
        $this->headers = [
            'Referer' => $this->baseUrl . 'xtgl/login_slogin.html',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
        ];
        $this->cookies = $cookies;
    }

    public function getInfo(): array
    {
        $url = $this->baseUrl . 'xsxxxggl/xsxxwh_cxCkDgxsxx.html?gnmkdm=N100801';
        $response = $this->request('GET', $url);
        if ($response['status'] !== 200) {
            return ['code' => 2333, 'msg' => '教务系统挂了'];
        }
        if (str_contains($response['body'], '用户登录')) {
            return ['code' => 1006, 'msg' => '未登录或已过期，请重新登录'];
        }
        $info = json_decode($response['body'], true);
        if (!is_array($info)) {
            return ['code' => 999, 'msg' => '接口逻辑或未知错误'];
        }
        $result = [
            'sid' => $info['xh'] ?? null,
            'name' => $info['xm'] ?? null,
            'college_name' => $info['zsjg_id'] ?? ($info['jg_id'] ?? null),
            'major_name' => $info['zszyh_id'] ?? ($info['zyh_id'] ?? null),
            'class_name' => $info['bh_id'] ?? ($info['xjztdm'] ?? null),
            'status' => $info['xjztdm'] ?? null,
            'enrollment_date' => $info['rxrq'] ?? null,
            'candidate_number' => $info['ksh'] ?? null,
            'graduation_school' => $info['byzx'] ?? null,
            'domicile' => $info['jg'] ?? null,
            'postal_code' => $info['yzbm'] ?? null,
            'politics_status' => $info['zzmmm'] ?? null,
            'nationality' => $info['mzm'] ?? null,
            'education' => $info['pyccdm'] ?? null,
            'phone_number' => $info['sjhm'] ?? null,
            'parents_number' => $info['gddh'] ?? null,
            'email' => $info['dzyx'] ?? null,
            'birthday' => $info['csrq'] ?? null,
            'id_number' => $info['zjhm'] ?? null,
        ];

        return ['code' => 1000, 'msg' => '获取个人信息成功', 'data' => $result];
    }

    public function getSchedule(int $year, int $term): array
    {
        $url = $this->baseUrl . 'kbcx/xskbcx_cxXsgrkb.html?gnmkdm=N2151';
        $payload = [
            'xnm' => $year,
            'xqm' => strval($term * 2 - 1),
        ];
        $data = $this->requestJson('POST', $url, $payload);
        if (isset($data['error'])) {
            return $data['error'];
        }

        return ['code' => 1000, 'msg' => '获取课表成功', 'data' => $data];
    }

    public function getGrade(int $year, int $term = 0): array
    {
        $url = $this->baseUrl . 'cjcx/cjcx_cxXsgrcj.html?doType=query&gnmkdm=N305005';
        $termCode = $term * $term * 3;
        $payload = [
            'xnm' => strval($year),
            'xqm' => $termCode === 0 ? '' : strval($termCode),
            '_search' => 'false',
            'nd' => strval((int) (microtime(true) * 1000)),
            'queryModel.showCount' => '100',
            'queryModel.currentPage' => '1',
            'queryModel.sortName' => '',
            'queryModel.sortOrder' => 'asc',
            'time' => '0',
        ];
        $data = $this->requestJson('POST', $url, $payload);
        if (isset($data['error'])) {
            return $data['error'];
        }
        $items = $data['items'] ?? [];
        if ($items === [] || !is_array($items)) {
            return ['code' => 1005, 'msg' => '获取内容为空'];
        }
        $courses = [];
        foreach ($items as $item) {
            $courses[] = [
                'course_id' => $item['kch_id'] ?? null,
                'title' => $item['kcmc'] ?? null,
                'teacher' => $item['jsxm'] ?? null,
                'class_name' => $item['jxbmc'] ?? null,
                'credit' => $this->alignFloats($item['xf'] ?? null),
                'category' => $item['kclbmc'] ?? null,
                'nature' => $item['kcxzmc'] ?? null,
                'grade' => $this->parseInt($item['cj'] ?? null),
                'grade_point' => $this->alignFloats($item['jd'] ?? null),
                'grade_nature' => $item['ksxz'] ?? null,
                'start_college' => $item['kkbmmc'] ?? null,
                'mark' => $item['kcbj'] ?? null,
            ];
        }

        return [
            'code' => 1000,
            'msg' => '获取成绩成功',
            'data' => [
                'sid' => $items[0]['xh'] ?? null,
                'name' => $items[0]['xm'] ?? null,
                'year' => $year,
                'term' => $term,
                'count' => count($items),
                'courses' => $courses,
            ],
        ];
    }

    public function getExamSchedule(int $year, int $term = 0): array
    {
        $url = $this->baseUrl . 'kwgl/kscx_cxXsksxxIndex.html?doType=query&gnmkdm=N358105';
        $termCode = $term * $term * 3;
        $payload = [
            'xnm' => strval($year),
            'xqm' => $termCode === 0 ? '' : strval($termCode),
            '_search' => 'false',
            'nd' => strval((int) (microtime(true) * 1000)),
            'queryModel.showCount' => '100',
            'queryModel.currentPage' => '1',
            'queryModel.sortName' => '',
            'queryModel.sortOrder' => 'asc',
            'time' => '0',
        ];
        $data = $this->requestJson('POST', $url, $payload);
        if (isset($data['error'])) {
            return $data['error'];
        }
        $items = $data['items'] ?? [];
        if ($items === [] || !is_array($items)) {
            return ['code' => 1005, 'msg' => '获取内容为空'];
        }
        $courses = [];
        foreach ($items as $item) {
            $courses[] = [
                'course_id' => $item['kch'] ?? null,
                'title' => $item['kcmc'] ?? null,
                'time' => $item['kssj'] ?? null,
                'location' => $item['cdmc'] ?? null,
                'xq' => $item['cdxqmc'] ?? null,
                'zwh' => $item['zwh'] ?? null,
                'cxbj' => $item['cxbj'] ?? '',
                'exam_name' => $item['ksmc'] ?? null,
                'teacher' => $item['jsxx'] ?? null,
                'class_name' => $item['jxbmc'] ?? null,
                'kkxy' => $item['kkxy'] ?? null,
                'credit' => $this->alignFloats($item['xf'] ?? null),
                'ksfs' => $item['ksfs'] ?? null,
                'sjbh' => $item['sjbh'] ?? null,
                'bz' => $item['bz1'] ?? '',
            ];
        }

        return [
            'code' => 1000,
            'msg' => '获取考试信息成功',
            'data' => [
                'sid' => $items[0]['xh'] ?? null,
                'name' => $items[0]['xm'] ?? null,
                'year' => $year,
                'term' => $term,
                'count' => count($items),
                'courses' => $courses,
            ],
        ];
    }

    private function request(string $method, string $url, array $payload = []): array
    {
        $ch = curl_init();
        $headers = [];
        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }
        $cookiePairs = [];
        foreach ($this->cookies as $key => $value) {
            $cookiePairs[] = $key . '=' . $value;
        }
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_COOKIE => implode('; ', $cookiePairs),
        ];
        if (strtoupper($method) === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($payload);
        }
        curl_setopt_array($ch, $options);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $status,
            'body' => is_string($body) ? $body : '',
        ];
    }

    private function requestJson(string $method, string $url, array $payload = []): array
    {
        $response = $this->request($method, $url, $payload);
        if ($response['status'] !== 200) {
            return ['error' => ['code' => 2333, 'msg' => '教务系统挂了']];
        }
        if (str_contains($response['body'], '用户登录')) {
            return ['error' => ['code' => 1006, 'msg' => '未登录或已过期，请重新登录']];
        }
        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            return ['error' => ['code' => 999, 'msg' => '接口逻辑或未知错误']];
        }

        return $data;
    }

    private function alignFloats($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function parseInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
