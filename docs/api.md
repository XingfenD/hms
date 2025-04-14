### 用户管理页面：
#### 获取用户数据 API

get_users.php  下面是个例子
```php
<?php
// 允许跨域请求
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

// 连接数据库
$conn = new mysqli("localhost", "username", "password", "database_name");
if ($conn->connect_error) {
    die(json_encode(["error" => "数据库连接失败: " . $conn->connect_error]));
}

// 获取医生数据
$doctorQuery = "SELECT id, name, department, title, status, lastLogin FROM doctors";
$doctorResult = $conn->query($doctorQuery);
$doctors = [];
if ($doctorResult->num_rows > 0) {
    while ($row = $doctorResult->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// 获取患者数据
$patientQuery = "SELECT id, name, gender, age, phone, status, lastLogin FROM patients";
$patientResult = $conn->query($patientQuery);
$patients = [];
if ($patientResult->num_rows > 0) {
    while ($row = $patientResult->fetch_assoc()) {
        $patients[] = $row;
    }
}

// 获取管理员数据
$adminQuery = "SELECT id, name, role, status, lastLogin FROM admins";
$adminResult = $conn->query($adminQuery);
$admins = [];
if ($adminResult->num_rows > 0) {
    while ($row = $adminResult->fetch_assoc()) {
        $admins[] = $row;
    }
}

// 关闭数据库连接
$conn->close();

// 返回数据
echo json_encode([
    "doctors" => $doctors,
    "patients" => $patients,
    "admins" => $admins
]);
```

#### 添加用户api

add_user.php
用于添加医生、患者或管理员用户。
你们只需要告诉我传什么参数可以添加什么用户就可以了

#### 批量导入用户api

用于批量导入用户数据。解析Excel或者CSV文件后，根据文件中的内容导入数据



###  医生排班页面

你们需要告诉我返回的是什么样的数据，我好提取

>小建议，建议对用户输入严格验证和过滤，不要用SQL注入的问题

#### 获取科室列表

用于获取系统中所有科室的信息。

#### 根据科室获取医生列表API

根据所选科室 ID 获取该科室下的医生信息

#### 获取指定日期范围内的排班信息 API

这个日期参数的格式告述我，我可以对应更改

获取指定日期范围内的医生排班信息

#### 添加排班信息 API

用于新增医生排班信息。

#### 编辑排班信息API

用于修改已有的医生排班信息。

#### 删除排班信息API

用于删除指定 ID 的医生排班信息



**下面用ai生成的可能会有重复的**

### 患者挂号查询页面

#### 1. 获取科室列表（这个前面实现过的话就不用实现了）

- **API 路径**：`/api/departments`
- **请求方法**：`GET`
- **功能**：返回所有科室的信息，包括科室 ID 和科室名称，用于前端科室选择下拉框的选项填充。

#### 2. 获取患者列表

- **API 路径**：`/api/patients`
- **请求方法**：`GET`
- **功能**：返回所有患者的信息，如患者 ID、姓名、性别、年龄、联系电话等，用于前端搜索患者和选择患者时的数据展示。

#### 3. 获取医生列表

- **API 路径**：`/api/doctors`
- **请求方法**：`GET`
- **功能**：返回所有医生的信息，包含医生 ID、姓名、职称、所属科室 ID 等，为前端选择医生提供数据支持。

#### 4. 根据科室 ID 获取医生列表

- **API 路径**：`/api/doctors?departmentId={departmentId}`
- **请求方法**：`GET`
- **功能**：根据传入的科室 ID，返回该科室下的所有医生信息，用于在用户选择科室后动态更新医生选择下拉框的选项。

#### 5. 根据条件搜索挂号记录

- **API 路径**：`/api/appointments/search`
- **请求方法**：`GET`
- 请求参数
  - `patientId`：患者 ID
  - `doctorId`：医生 ID
  - `departmentId`：科室 ID
  - `startDate`：开始日期
  - `endDate`：结束日期
  - `status`：挂号状态（已预约、已完成、已取消）
- **功能**：根据传入的搜索条件，返回符合条件的挂号记录列表，包含挂号 ID、患者信息、医生信息、挂号类型、预约时间、状态等，用于前端表格数据展示。

#### 6. 创建挂号记录

- **API 路径**：`/api/appointments`

- **请求方法**：`POST`

- 请求参数

  ：

  - `patientId`：患者 ID
  - `doctorId`：医生 ID
  - `departmentId`：科室 ID
  - `type`：挂号类型（普通号、专家号、急诊）
  - `appointmentDate`：预约日期
  - `appointmentTime`：预约时间
  - `remark`：备注信息

- **功能**：根据传入的挂号信息，在数据库中创建一条新的挂号记录，并返回创建成功的挂号记录信息。

#### 7. 完成挂号记录

- **API 路径**：`/api/appointments/{appointmentId}/complete`
- **请求方法**：`PUT`
- **功能**：将指定 ID 的挂号记录状态更新为 “已完成”。

#### 8. 取消挂号记录

- **API 路径**：`/api/appointments/{appointmentId}/cancel`
- **请求方法**：`PUT`
- **功能**：将指定 ID 的挂号记录状态更新为 “已取消”。

#### 9. 导出挂号记录为 Excel

- **API 路径**：`/api/appointments/export`
- **请求方法**：`GET`
- **请求参数**：可携带搜索条件参数（同搜索挂号记录的参数）
- **功能**：根据传入的条件筛选挂号记录，并将符合条件的记录导出为 Excel 文件。



### 药品管理相关 API

#### 1. 获取药品列表

- **API 路径**：`/api/drugs`

- **请求方法**：`GET`

- 请求参数

  ：

  - `keyword`：搜索关键字，用于模糊搜索药品名称、ID 或生产厂家。

- **功能**：返回符合搜索条件的药品列表，包含药品 ID、名称、规格、库存数量、单价、生产厂家、药品类别、药品说明等信息。

#### 2. 新增药品

- **API 路径**：`/api/drugs`

- **请求方法**：`POST`

- 请求参数

  ：

  - `name`：药品名称
  - `specification`：药品规格
  - `stock`：库存数量
  - `price`：单价
  - `manufacturer`：生产厂家
  - `category`：药品类别
  - `description`：药品说明

- **功能**：在数据库中创建一条新的药品记录，并返回新增药品的信息。

#### 3. 编辑药品信息

- **API 路径**：`/api/drugs/{drugId}`

- **请求方法**：`PUT`

- 请求参数

  ：

  - `name`：药品名称
  - `specification`：药品规格
  - `stock`：库存数量
  - `price`：单价
  - `manufacturer`：生产厂家
  - `category`：药品类别
  - `description`：药品说明

- **功能**：根据传入的药品 ID，更新该药品的信息，并返回更新后的药品信息。

#### 4. 删除药品

- **API 路径**：`/api/drugs/{drugId}`
- **请求方法**：`DELETE`
- **功能**：根据传入的药品 ID，从数据库中删除该药品记录。

#### 5. 药品补货

- **API 路径**：`/api/drugs/{drugId}/stock`

- **请求方法**：`PUT`

- 请求参数

  ：

  - `quantity`：补货数量
  - `reason`：补货原因

- **功能**：根据传入的药品 ID，增加该药品的库存数量，并记录补货原因。

### 处方管理相关 API

#### 1. 获取待发药处方列表

- **API 路径**：`/api/prescriptions`

- **请求方法**：`GET`

- 请求参数

  ：

  - `keyword`：搜索关键字，用于模糊搜索患者姓名、医生姓名、药品名称或处方 ID。

- **功能**：返回符合搜索条件的待发药处方列表，包含处方 ID、患者信息、医生信息、药品信息、数量、总价、状态、处方日期等信息。

#### 2. 发药操作

- **API 路径**：`/api/prescriptions/{prescriptionId}/dispense`
- **请求方法**：`PUT`
- **功能**：根据传入的处方 ID，将该处方的状态更新为 “已发药”，并减少对应药品的库存数量。

#### 3. 批量发药操作

- **API 路径**：`/api/prescriptions/batch-dispense`

- **请求方法**：`PUT`

- 请求参数

  ：

  - `prescriptionIds`：处方 ID 数组

- **功能**：根据传入的处方 ID 数组，将这些处方的状态更新为 “已发药”，并减少对应药品的库存数量。



### 数据库管理页面

### 备份管理相关 API

#### 1. 执行数据库备份

- **API 路径**：`/api/database/backup`

- **请求方法**：`POST`

- 请求参数

  ：

  - `backupPath`：备份文件保存路径，若为空则使用默认路径。

- **功能**：对当前数据库进行备份操作，将备份数据保存到指定路径下的 SQL 文件中，并返回备份文件的相关信息（如文件名、文件大小、备份时间等）。

#### 2. 获取备份历史记录

- **API 路径**：`/api/database/backup/history`
- **请求方法**：`GET`
- **功能**：返回数据库备份的历史记录列表，包含备份文件的 ID、文件名、路径、文件大小、备份时间等信息。

#### 3. 下载备份文件

- **API 路径**：`/api/database/backup/download/{backupId}`
- **请求方法**：`GET`
- **功能**：根据传入的备份文件 ID，返回对应的备份 SQL 文件供前端下载。

#### 4. 删除备份文件

- **API 路径**：`/api/database/backup/delete/{backupId}`
- **请求方法**：`DELETE`
- **功能**：根据传入的备份文件 ID，从服务器中删除对应的备份 SQL 文件。

### 恢复管理相关 API

#### 1. 执行数据库恢复

- **API 路径**：`/api/database/restore`

- **请求方法**：`POST`

- 请求参数

  ：

  - `file`：要恢复的 SQL 备份文件。

- **功能**：使用上传的 SQL 备份文件对当前数据库进行恢复操作，覆盖数据库中的现有数据，并返回恢复操作的结果信息。

