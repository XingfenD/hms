<template>
    <div class="flex flex-col space-y-4 p-4 bg-white shadow-md rounded-md">
      <a-button type="primary" @click="showModal = true">智能排班</a-button>
      <a-modal
        title="选择排班日期"
        :visible="showModal"
        @ok="handleOk"
        @cancel="showModal = false"
      >
        <div class="flex space-x-4">
          <a-date-picker v-model:value="startDate" format="YYYY-MM-DD" placeholder="开始日期" />
          <a-date-picker v-model:value="endDate" format="YYYY-MM-DD" placeholder="结束日期" />
        </div>
      </a-modal>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  import { message, DatePicker, Modal, Button } from 'ant-design-vue';
  
  const AButton = Button;
  const ADatePicker = DatePicker;
  const AModal = Modal;
  
  const processButton = ref(null);
  const startDate = ref(null);
  const endDate = ref(null);
  const showModal = ref(false);
  
  const promptTemplate = `下面是一份医生信息：
  {doctorsJson}
  
  请你根据这份信息，为每位医生安排从 {startDate} 到 {endDate} 的班次，每天时间从 08:30:00 到 次日 07:00:00，其中只能有三个班次，分别为 08:30:00 到 12:00:00，14:00:00 到 17:30:00 和 18:00:00 到 07:00:00。
  请严格按照以下 JSON 格式返回排班结果（只返回 JSON 内容，不要包含任何其他文字解释），每个医生每天一条记录，包含 \`doc_id\` (对应原始数据的 DoctorID)，\`date\`, \`start_time\`, \`end_time\` 字段。确保 \`schedule_id\` 留空或为 null，数据库会自动生成。
  
  示例格式：
  [
    {
      "schedule_id": null,
      "doc_id": 1,
      "date": "2025-04-28",
      "start_time": "08:30:00",
      "end_time": "12:00:00"
    },
    {
      "schedule_id": null,
      "doc_id": 2,
      "date": "2025-04-28",
      "start_time": "18:00:00",
      "end_time": "07:00:00"
    }
  ]`;
  
  const updateStatus = (messageText, isError = false, isSuccess = false) => {
    console.log(messageText);
    if (isError) {
      message.error(messageText);
    } else if (isSuccess) {
      message.success(messageText);
    } else {
      message.info(messageText);
    }
  };
  
  const handleSmartScheduling = async () => {
    if (!startDate.value || !endDate.value) {
      updateStatus("请选择开始日期和结束日期", true);
      return;
    }
    
    if (processButton.value) {
      processButton.value.disabled = true;
    }
    
    updateStatus("开始处理...");
  
    let doctorsData = null;
  
    // 步骤 1: 获取医生信息
    try {
      updateStatus("步骤 1: 获取医生信息...");
      const response = await fetch('http://localhost/hms_new/apis/ai_system/get_doctors.php');
      if (!response.ok) {
        throw new Error(`HTTP 错误! 状态: ${response.status}`);
      }
      doctorsData = await response.json();
      if (doctorsData.error) {
        throw new Error(`获取医生数据时服务器返回错误: ${doctorsData.error}`);
      }
      if (!Array.isArray(doctorsData) || doctorsData.length === 0) {
        throw new Error("未获取到有效的医生数据或数据为空数组。");
      }
      updateStatus(`成功获取 ${doctorsData.length} 条医生信息。`);
      console.log("医生数据:", doctorsData);
    } catch (error) {
      updateStatus(`步骤 1 失败: ${error.message}`, true);
      if (processButton.value) {
        processButton.value.disabled = false;
      }
      return;
    }
  
    // 步骤 2: 调用 AI API
    let aiScheduleResponse = null;
    try {
      updateStatus("步骤 2: 准备并发送请求至 AI...");
      if (!promptTemplate.includes('{doctorsJson}') || !promptTemplate.includes('{startDate}') || !promptTemplate.includes('{endDate}')) {
        throw new Error("Prompt 模板中必须包含占位符 {doctorsJson}、{startDate} 和 {endDate}");
      }
      
      const doctorsJsonString = JSON.stringify(doctorsData, null, 2);
      const finalPrompt = promptTemplate
        .replace('{doctorsJson}', doctorsJsonString)
        .replace('{startDate}', startDate.value.format('YYYY-MM-DD'))
        .replace('{endDate}', endDate.value.format('YYYY-MM-DD'));
  
      updateStatus("向 AI 发送的最终 Prompt (Message):");
      updateStatus(finalPrompt);
  
      const aiResponse = await fetch('http://localhost:8000/chat', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          message: finalPrompt,
          session_id: null
        }),
      });
  
      if (!aiResponse.ok) {
        throw new Error(`AI API HTTP 错误! 状态: ${aiResponse.status}`);
      }
  
      const aiResult = await aiResponse.json();
      updateStatus("收到 AI 的响应。");
      console.log("AI 原始响应:", aiResult);
  
      if (!aiResult || typeof aiResult.message !== 'string') {
        throw new Error("AI 响应格式无效，缺少 'message' 字符串。");
      }
  
      // 尝试解析 AI 返回的排班 JSON
      updateStatus("尝试解析 AI 返回的排班 JSON...");
      try {
        aiScheduleResponse = JSON.parse(aiResult.message);
        updateStatus("成功解析 AI 返回的排班信息。");
        console.log("解析后的排班数据:", aiScheduleResponse);
      } catch (parseError) {
        updateStatus(`解析 AI 返回的 message 失败: ${parseError.message}`, true);
        updateStatus("AI 返回的原始 message 内容:", true);
        updateStatus(aiResult.message, true);
        throw new Error("AI 返回的 message 不是有效的 JSON 格式。");
      }
  
      // 基本验证 AI 响应结构
      if (!Array.isArray(aiScheduleResponse)) {
        throw new Error("AI 返回的排班数据不是一个有效的 JSON 数组。");
      }
      if (aiScheduleResponse.length === 0) {
        updateStatus("AI 返回了空的排班数组，无需保存。", false, true);
        if (processButton.value) {
          processButton.value.disabled = false;
        }
        return;
      }
      const firstItem = aiScheduleResponse[0];
      const requiredKeys = ['doc_id', 'date', 'start_time', 'end_time'];
      const missingKeys = requiredKeys.filter(key => !(key in firstItem));
      if (missingKeys.length > 0) {
        throw new Error(`AI 返回的排班数据缺少必要字段: ${missingKeys.join(', ')}`);
      }
    } catch (error) {
      updateStatus(`步骤 2 失败: ${error.message}`, true);
      if (processButton.value) {
        processButton.value.disabled = false;
      }
      return;
    }
  
    // 步骤 3: 保存排班到数据库
    try {
      updateStatus("步骤 3: 发送排班数据到服务器保存...");
  
      const saveResponse = await fetch('http://localhost/hms_new/apis/ai_system/save_schedule.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(aiScheduleResponse),
      });
  
      if (!saveResponse.ok) {
        let errorText = `HTTP 错误! 状态: ${saveResponse.status}`;
        try {
          const errorJson = await saveResponse.json();
          if (errorJson.error) errorText += ` - ${errorJson.error}`;
        } catch (e) { }
        throw new Error(errorText);
      }
  
      const saveResult = await saveResponse.json();
  
      if (saveResult.success) {
        updateStatus(`步骤 3 成功: ${saveResult.message}`, false, true);
      } else {
        throw new Error(`保存排班时服务器返回错误: ${saveResult.error || '未知错误'}`);
      }
    } catch (error) {
      updateStatus(`步骤 3 失败: ${error.message}`, true);
    } finally {
      if (processButton.value) {
        processButton.value.disabled = false;
      }
    }
  };
  
  const handleOk = () => {
    showModal.value = false;
    handleSmartScheduling();
  };
  </script>
  
  <style scoped>
  /* .flex {
    display: flex;
  }
  .flex-col {
    flex-direction: column;
  }
  .space-y-4 > * + * {
    margin-top: 1rem;
  }
  .space-x-4 > * + * {
    margin-left: 1rem;
  }
  .p-4 {
    padding: 1rem;
  }
  .bg-white {
    background-color: white;
  }
  .shadow-md {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  }
  .rounded-md {
    border-radius: 0.375rem;
  } */
  </style>