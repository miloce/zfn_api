🏫 新正方教务管理系统 API（PHP 版）

😁 几乎规避所有学校不同导致的兼容性问题，可放心食用！！

<!-- > ⚠️ 原 Django-WebAPI 项目：[jokerwho/zfnew_wenApi](https://github.com/jokerwho/zfnew_webApi) 已停止更新，后续 API 更新将在本项目进行 -->

求 ⭐⭐⭐⭐⭐（跪

---

## 功能实现

- [x] 使用现有 cookies 调用接口（PHP 版不包含登录流程）
- [x] 个人信息
- [x] 成绩查询
- [x] 考试信息查询
- [x] 课表查询

## 状态码

为了一些特殊的业务逻辑，如验证码错误后自动刷新页面获取等，使用了自定义状态码，详情如下：

| 状态码 | 内容                 |
| ------ | -------------------- |
| 998    | 网页弹窗未处理内容   |
| 999    | 接口逻辑或未知错误   |
| 1000   | 请求获取成功         |
| 1001   | （登录）需要验证码   |
| 1002   | 用户名或密码不正确   |
| 1003   | 请求超时             |
| 1004   | 验证码错误           |
| 1005   | 内容为空             |
| 1006   | cookies 失效或过期   |
| 1007   | 接口失效请更新       |
| 2333   | 系统维护或服务被 ban |

## Tips⚠️

- 上下课时间 `raspisanie` 若与 `zfn_api.php` 中不一致，请自行按照相关格式编写。
- 学业生涯数据为教务系统 **“学生学业情况查询”** 页面内容，获取数据时请留意 `ignore_type` 和 `detail_category_type`。
  - `ignore_type` 表示需要忽略的最顶部根类型，如 “主修”，“20XX 级 XX 专业” 等无用类型，**可留空数组，对结果无影响**。
  - `detail_category_type` 表示需要详细获取课程分类的类型，如 “其他课程” 需获取该网课属于什么类等，**可留空数组**。
- 教务系统的 cookies 在不同学校统一认证系统不同，**若系统开启了验证码且 cookies 格式内容与默认有出入**，请确保传入完整 cookies。
- 兼容导致 学业生涯数据 PDF 表的导出会出现问题，待排查。
## PHP 使用示例（直接使用已有 cookies）

```php
<?php
require_once __DIR__ . '/zfn_api.php';

$client = new ZfnClient([
    'cookies' => [
        'JSESSIONID' => 'your_session_id',
        'route' => 'your_route_cookie',
    ],
    'timeout' => 5,
]);

$info = $client->getInfo();
var_dump($info);

$grade = $client->getGrade(2024, 1);
var_dump($grade);

$exam = $client->getExamSchedule(2024, 1);
var_dump($exam);

$schedule = $client->getSchedule(2024, 1);
var_dump($schedule);
```

## 部分数据字段说明

```json
{
  // 成绩
  "course_id": "课程号",
  "title": "课程标题",
  "teacher": "任课教师",
  "class_name": "教学班名称",
  "credit": "学分",
  "category": "课程类别",
  "nature": "课程性质",
  "grade": "成绩",
  "grade_point": "绩点",
  "grade_nature": "成绩性质",
  "start_college": "开课院系",
  "mark": "",
  // 课表
  "weekday": "星期几",
  "time": "上课时间",
  "sessions": "上课节数",
  "list_sessions": "开课节数列表",
  "weeks": "开课周数",
  "list_weeks": "开课周数列表",
  "evaluation_mode": "考核方式",
  "campus": "上课校区",
  "place": "上课场地",
  "hours_composition": "课程学时组成",
  "weekly_hours": "每周学时",
  "total_hours": "总学时",
  // 学业生涯
  "situation": "修读情况",
  "display_term": "修读学期",
  "max_grade": "最佳成绩",
  // 选课
  "class_id": "教学班ID",
  "do_id": "执行ID",
  "teacher_id": "教师ID",
  "kklxdm": "板块课ID",
  "capacity": "教学班容量",
  "selected_number": "已选人数",
  "optional": "是否自选",
  "waiting": "",
  // 考试日程
  "course_id": "课程号",
  "title": "课程名称",
  "time": "考试时间",
  "location": "考试地点",
  "xq": "考试校区",
  "zwh": "考试座号",
  "cxbj": "重修标记",
  "exam_name": "考试名称(如:2023-2024-1学期期末考试)",
  "teacher": "任课教师",
  "class_name": "教学班名称",
  "kkxy": "开课学院",
  "credit": "学分",
  "ksfs": "考试方式(如:笔试,开卷,机考)",
  "sjbh": "试卷编号",
  "bz": "备注",
}
```

## 星图

[![Stargazers over time](https://starchart.cc/openschoolcn/zfn_api.svg)](https://starchart.cc/openschoolcn/zfn_api)
