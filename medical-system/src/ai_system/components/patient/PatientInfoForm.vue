<template>
  <div class="patient-info-form">
    <div class="form-header">
      <h2>患者信息</h2>
      <div class="filter-controls">
        <div class="filter-toggle" @click="showFilterPanel = !showFilterPanel">
          <span>筛选字段</span>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 9l6 6 6-6"/>
          </svg>
        </div>
        
        <div class="filter-panel" v-if="showFilterPanel">
          <div class="filter-options">
            <div class="filter-section">
              <h3>按重要性筛选</h3>
              <div class="filter-buttons">
                <button 
                  @click="applyImportanceFilter('essential')" 
                  :class="{ active: currentFilter === 'essential' }"
                >必填字段</button>
                <button 
                  @click="applyImportanceFilter('recommended')" 
                  :class="{ active: currentFilter === 'recommended' }"
                >推荐字段</button>
                <button 
                  @click="applyImportanceFilter('all')" 
                  :class="{ active: currentFilter === 'all' }"
                >所有字段</button>
              </div>
            </div>
            
            <div class="filter-section">
              <h3>按类别筛选</h3>
              <div class="category-checkboxes">
                <div v-for="(category, key) in categories" :key="key" class="category-checkbox">
                  <input 
                    type="checkbox" 
                    :id="key" 
                    :checked="selectedCategories.includes(key)"
                    @change="toggleCategory(key)"
                  >
                  <label :for="key">{{ category.label }}</label>
                </div>
              </div>
            </div>
            
            <div class="filter-section">
              <h3>快速模板</h3>
              <div class="template-buttons">
                <button @click="applyTemplate('common')">常见症状</button>
                <button @click="applyTemplate('chronic')">慢性病</button>
                <button @click="applyTemplate('emergency')">急诊</button>
              </div>
            </div>
          </div>
          
          <div class="filter-actions">
            <button class="reset-button" @click="resetFilters">重置筛选</button>
            <button class="apply-button" @click="showFilterPanel = false">应用筛选</button>
          </div>
        </div>
      </div>
    </div>
    
    <div class="form-content">
      <div v-for="(category, categoryKey) in filteredCategories" :key="categoryKey" class="form-category">
        <div class="category-header" @click="toggleCategoryExpand(categoryKey)">
          <h3>{{ category.label }}</h3>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ 'expanded': expandedCategories.includes(categoryKey) }">
            <path d="M6 9l6 6 6-6"/>
          </svg>
        </div>
        
        <div class="category-fields" v-if="expandedCategories.includes(categoryKey)">
          <div v-for="field in category.fields" :key="field.key" class="form-field" v-show="isFieldVisible(field)">
            <label :for="field.key">
              {{ field.label }}
              <span v-if="field.required" class="required-mark">*</span>
            </label>
            
            <!-- 文本输入 -->
            <input 
              v-if="field.type === 'text'" 
              type="text" 
              :id="field.key" 
              v-model="patientInfo[categoryKey][field.key]"
              :placeholder="field.placeholder || ''"
            >
            
            <!-- 数字输入 -->
            <div v-else-if="field.type === 'number'" class="number-input">
              <input 
                type="number" 
                :id="field.key" 
                v-model="patientInfo[categoryKey][field.key]"
                :placeholder="field.placeholder || ''"
                :min="field.min"
                :max="field.max"
                :step="field.step || 1"
              >
              <span v-if="field.unit" class="input-unit">{{ field.unit }}</span>
            </div>
            
            <!-- 多行文本 -->
            <textarea 
              v-else-if="field.type === 'textarea'" 
              :id="field.key" 
              v-model="patientInfo[categoryKey][field.key]"
              :placeholder="field.placeholder || ''"
              :rows="field.rows || 3"
            ></textarea>
            
            <!-- 单选按钮 -->
            <div v-else-if="field.type === 'radio'" class="radio-group">
              <div v-for="option in field.options" :key="option.value" class="radio-option">
                <input 
                  type="radio" 
                  :id="`${field.key}_${option.value}`" 
                  :name="field.key" 
                  :value="option.value"
                  v-model="patientInfo[categoryKey][field.key]"
                >
                <label :for="`${field.key}_${option.value}`">{{ option.label }}</label>
              </div>
            </div>
            
            <!-- 复选框 -->
            <div v-else-if="field.type === 'checkbox'" class="checkbox-group">
              <div v-for="option in field.options" :key="option.value" class="checkbox-option">
                <input 
                  type="checkbox" 
                  :id="`${field.key}_${option.value}`" 
                  :value="option.value"
                  v-model="patientInfo[categoryKey][field.key]"
                >
                <label :for="`${field.key}_${option.value}`">{{ option.label }}</label>
              </div>
            </div>
            
            <!-- 滑块 -->
            <div v-else-if="field.type === 'slider'" class="slider-input">
              <input 
                type="range" 
                :id="field.key" 
                v-model.number="patientInfo[categoryKey][field.key]"
                :min="field.min || 1"
                :max="field.max || 10"
                :step="field.step || 1"
              >
              <div class="slider-value">{{ patientInfo[categoryKey][field.key] || 0 }}</div>
            </div>
            
            <!-- 复合字段 (如持续时间) -->
            <div v-else-if="field.type === 'compound'" class="compound-field">
              <input 
                type="number" 
                :id="`${field.key}_value`" 
                v-model.number="patientInfo[categoryKey][field.key].value"
                :min="field.min || 0"
                :step="field.step || 1"
              >
              <select v-model="patientInfo[categoryKey][field.key].unit">
                <option v-for="unit in field.units" :key="unit.value" :value="unit.value">
                  {{ unit.label }}
                </option>
              </select>
            </div>
            
            <div v-if="field.description" class="field-description">
              {{ field.description }}
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="form-actions">
      <button class="reset-button" @click="resetForm">重置表单</button>
      <button class="save-button" @click="savePatientInfo">保存信息</button>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';

export default {
  name: 'PatientInfoForm',
  emits: ['update:patientInfo', 'save'],
  
  setup(props, { emit }) {
    // 表单筛选状态
    const showFilterPanel = ref(false);
    const currentFilter = ref('recommended');
    const selectedCategories = ref([
      'basic', 'symptoms', 'medication', 'allergiesAndContraindications'
    ]);
    const expandedCategories = ref(['basic', 'symptoms']);
    
    // 初始化患者信息数据结构
    const patientInfo = reactive({
      // 基本信息
      basic: {
        name: "",
        gender: "",
        age: null,
        height: null,
        weight: null,
        bloodType: "",
        contact: ""
      },
      
      // 主诉与症状
      symptoms: {
        mainSymptoms: "",
        duration: {
          value: null,
          unit: "天"
        },
        severity: null,
        trend: "",
        triggerFactors: "",
        relievingFactors: ""
      },
      
      // 既往病史
      medicalHistory: {
        chronicDiseases: [],
        surgeryHistory: "",
        hospitalizationHistory: "",
        traumaHistory: "",
        infectiousDiseases: []
      },
      
      // 用药情况
      medication: {
        currentMedications: "",
        dosage: "",
        frequency: "",
        duration: "",
        compliance: ""
      },
      
      // 过敏与禁忌
      allergiesAndContraindications: {
        drugAllergies: "",
        foodAllergies: "",
        otherAllergies: "",
        specialContraindications: ""
      },
      
      // 家族病史
      familyHistory: {
        geneticDiseases: "",
        firstDegreeRelativeDiseases: ""
      },
      
      // 生活习惯
      lifestyle: {
        smoking: "",
        drinking: "",
        dietaryHabits: [],
        exerciseHabits: "",
        sleepQuality: ""
      },
      
      // 心理状态
      mentalState: {
        psychologicalState: "",
        stressLevel: null,
        moodChanges: ""
      },
      
      // 就诊信息
      medicalVisits: {
        previousVisits: "",
        examinationResults: "",
        diagnosis: "",
        treatmentPlan: ""
      }
    });
    
    // 表单字段定义
    const categories = {
      basic: {
        label: '基本信息',
        fields: [
          { 
            key: 'name', 
            label: '姓名', 
            type: 'text',
            importance: 'recommended',
            placeholder: '请输入姓名（选填）'
          },
          { 
            key: 'gender', 
            label: '性别', 
            type: 'radio',
            importance: 'recommended',
            options: [
              { value: '男', label: '男' },
              { value: '女', label: '女' },
              { value: '其他', label: '其他' },
              { value: '不愿透露', label: '不愿透露' }
            ]
          },
          { 
            key: 'age', 
            label: '年龄', 
            type: 'number',
            importance: 'essential',
            min: 0,
            max: 120,
            placeholder: '请输入年龄'
          },
          { 
            key: 'height', 
            label: '身高', 
            type: 'number',
            importance: 'recommended',
            min: 0,
            max: 250,
            unit: 'cm',
            placeholder: '请输入身高'
          },
          { 
            key: 'weight', 
            label: '体重', 
            type: 'number',
            importance: 'recommended',
            min: 0,
            max: 500,
            unit: 'kg',
            placeholder: '请输入体重'
          },
          { 
            key: 'bloodType', 
            label: '血型', 
            type: 'radio',
            importance: 'optional',
            options: [
              { value: 'A', label: 'A型' },
              { value: 'B', label: 'B型' },
              { value: 'AB', label: 'AB型' },
              { value: 'O', label: 'O型' },
              { value: '不确定', label: '不确定' }
            ]
          },
          { 
            key: 'contact', 
            label: '联系方式', 
            type: 'text',
            importance: 'optional',
            placeholder: '请输入联系电话（选填）'
          }
        ]
      },
      
      symptoms: {
        label: '主诉与症状',
        fields: [
          { 
            key: 'mainSymptoms', 
            label: '主要症状', 
            type: 'textarea',
            importance: 'essential',
            placeholder: '请详细描述您的症状',
            rows: 4
          },
          { 
            key: 'duration', 
            label: '症状持续时间', 
            type: 'compound',
            importance: 'recommended',
            units: [
              { value: '小时', label: '小时' },
              { value: '天', label: '天' },
              { value: '周', label: '周' },
              { value: '月', label: '月' },
              { value: '年', label: '年' }
            ]
          },
          { 
            key: 'severity', 
            label: '症状严重程度', 
            type: 'slider',
            importance: 'recommended',
            min: 1,
            max: 10,
            description: '1表示轻微，10表示非常严重'
          },
          { 
            key: 'trend', 
            label: '症状变化趋势', 
            type: 'radio',
            importance: 'recommended',
            options: [
              { value: '加重', label: '加重' },
              { value: '稳定', label: '稳定' },
              { value: '缓解', label: '缓解' },
              { value: '波动', label: '波动' }
            ]
          },
          { 
            key: 'triggerFactors', 
            label: '诱发或加重因素', 
            type: 'textarea',
            importance: 'optional',
            placeholder: '有什么因素会诱发或加重症状？（选填）'
          },
          { 
            key: 'relievingFactors', 
            label: '缓解因素', 
            type: 'textarea',
            importance: 'optional',
            placeholder: '有什么因素会缓解症状？（选填）'
          }
        ]
      },
      
      medicalHistory: {
        label: '既往病史',
        fields: [
          { 
            key: 'chronicDiseases', 
            label: '慢性疾病', 
            type: 'checkbox',
            importance: 'recommended',
            options: [
              { value: '高血压', label: '高血压' },
              { value: '糖尿病', label: '糖尿病' },
              { value: '心脏病', label: '心脏病' },
              { value: '哮喘', label: '哮喘' },
              { value: '癌症', label: '癌症' },
              { value: '其他', label: '其他' }
            ]
          },
          { 
            key: 'surgeryHistory', 
            label: '手术史', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请描述您的手术经历（选填）'
          },
          { 
            key: 'hospitalizationHistory', 
            label: '住院史', 
            type: 'textarea',
            importance: 'optional',
            placeholder: '请描述您的住院经历（选填）'
          },
          { 
            key: 'traumaHistory', 
            label: '外伤史', 
            type: 'textarea',
            importance: 'optional',
            placeholder: '请描述您的外伤经历（选填）'
          },
          { 
            key: 'infectiousDiseases', 
            label: '传染病史', 
            type: 'checkbox',
            importance: 'optional',
            options: [
              { value: '肝炎', label: '肝炎' },
              { value: '结核', label: '结核' },
              { value: '其他', label: '其他' }
            ]
          }
        ]
      },
      
      medication: {
        label: '用药情况',
        fields: [
          { 
            key: 'currentMedications', 
            label: '当前用药', 
            type: 'textarea',
            importance: 'essential',
            placeholder: '请列出您目前正在服用的所有药物'
          },
          { 
            key: 'dosage', 
            label: '用药剂量', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请描述各药物的剂量（选填）'
          },
          { 
            key: 'frequency', 
            label: '用药频率', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请描述各药物的服用频率（选填）'
          },
          { 
            key: 'duration', 
            label: '用药时长', 
            type: 'textarea',
            importance: 'optional',
            placeholder: '请描述各药物的服用时长（选填）'
          },
          { 
            key: 'compliance', 
            label: '药物依从性', 
            type: 'radio',
            importance: 'recommended',
            options: [
              { value: '严格按医嘱', label: '严格按医嘱' },
              { value: '偶尔漏服', label: '偶尔漏服' },
              { value: '经常漏服', label: '经常漏服' },
              { value: '已停药', label: '已停药' }
            ]
          }
        ]
      },
      
      allergiesAndContraindications: {
        label: '过敏与禁忌',
        fields: [
          { 
            key: 'drugAllergies', 
            label: '药物过敏', 
            type: 'textarea',
            importance: 'essential',
            placeholder: '请列出您对哪些药物过敏'
          },
          { 
            key: 'foodAllergies', 
            label: '食物过敏', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请列出您对哪些食物过敏（选填）'
          },
          { 
            key: 'otherAllergies', 
            label: '其他过敏', 
            type: 'textarea',
            importance: 'optional',
            placeholder: '请列出您的其他过敏情况（选填）'
          },
          { 
            key: 'specialContraindications', 
            label: '特殊禁忌', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请描述您的特殊禁忌情况（选填）'
          }
        ]
      },
      
      familyHistory: {
        label: '家族病史',
        fields: [
          { 
            key: 'geneticDiseases', 
            label: '遗传性疾病', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请描述家族中的遗传性疾病（选填）'
          },
          { 
            key: 'firstDegreeRelativeDiseases', 
            label: '直系亲属疾病', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请描述直系亲属（父母、兄弟姐妹、子女）的主要疾病（选填）'
          }
        ]
      },
      
      lifestyle: {
        label: '生活习惯',
        fields: [
          { 
            key: 'smoking', 
            label: '吸烟情况', 
            type: 'radio',
            importance: 'recommended',
            options: [
              { value: '不吸烟', label: '不吸烟' },
              { value: '偶尔吸烟', label: '偶尔吸烟' },
              { value: '经常吸烟', label: '经常吸烟' },
              { value: '已戒烟', label: '已戒烟' }
            ]
          },
          { 
            key: 'drinking', 
            label: '饮酒情况', 
            type: 'radio',
            importance: 'recommended',
            options: [
              { value: '不饮酒', label: '不饮酒' },
              { value: '偶尔饮酒', label: '偶尔饮酒' },
              { value: '经常饮酒', label: '经常饮酒' },
              { value: '已戒酒', label: '已戒酒' }
            ]
          },
          { 
            key: 'dietaryHabits', 
            label: '饮食习惯', 
            type: 'checkbox',
            importance: 'optional',
            options: [
              { value: '素食', label: '素食' },
              { value: '高盐', label: '高盐' },
              { value: '高糖', label: '高糖' },
              { value: '高脂', label: '高脂' },
              { value: '辛辣', label: '辛辣' }
            ]
          },
          { 
            key: 'exerciseHabits', 
            label: '运动习惯', 
            type: 'textarea',
            importance: 'optional',
            placeholder: '请描述您的运动习惯（选填）'
          },
          { 
            key: 'sleepQuality', 
            label: '睡眠质量', 
            type: 'radio',
            importance: 'optional',
            options: [
              { value: '良好', label: '良好' },
              { value: '一般', label: '一般' },
              { value: '较差', label: '较差' },
              { value: '很差', label: '很差' }
            ]
          }
        ]
      },
      
      mentalState: {
        label: '心理状态',
        fields: [
          { 
            key: 'psychologicalState', 
            label: '心理状态', 
            type: 'textarea',
            importance: 'optional',
            placeholder: '请描述您的心理状态（选填）'
          },
          { 
            key: 'stressLevel', 
            label: '压力水平', 
            type: 'slider',
            importance: 'optional',
            min: 1,
            max: 10,
            description: '1表示无压力，10表示极度压力'
          },
          { 
            key: 'moodChanges', 
            label: '情绪变化', 
            type: 'textarea',
            importance: 'optional',
            placeholder: '请描述您的情绪变化情况（选填）'
          }
        ]
      },
      
      medicalVisits: {
        label: '就诊信息',
        fields: [
          { 
            key: 'previousVisits', 
            label: '既往就诊', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请描述您之前的就诊情况（选填）'
          },
          { 
            key: 'examinationResults', 
            label: '检查结果', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请描述您的检查结果（选填）'
          },
          { 
            key: 'diagnosis', 
            label: '既往诊断', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请描述您的既往诊断（选填）'
          },
          { 
            key: 'treatmentPlan', 
            label: '治疗方案', 
            type: 'textarea',
            importance: 'recommended',
            placeholder: '请描述您的治疗方案（选填）'
          }
        ]
      }
    };
    
    // 根据筛选条件过滤类别
    const filteredCategories = computed(() => {
      const result = {};
      
      for (const categoryKey of selectedCategories.value) {
        if (categories[categoryKey]) {
          result[categoryKey] = {
            ...categories[categoryKey],
            fields: categories[categoryKey].fields.filter(field => 
              currentFilter.value === 'all' || 
              field.importance === currentFilter.value || 
              (currentFilter.value === 'recommended' && field.importance === 'essential')
            )
          };
        }
      }
      
      return result;
    });
    
    // 判断字段是否可见
    const isFieldVisible = (field) => {
      return currentFilter.value === 'all' || 
             field.importance === currentFilter.value || 
             (currentFilter.value === 'recommended' && field.importance === 'essential');
    };
    
    // 切换类别展开状态
    const toggleCategoryExpand = (categoryKey) => {
      const index = expandedCategories.value.indexOf(categoryKey);
      if (index === -1) {
        expandedCategories.value.push(categoryKey);
      } else {
        expandedCategories.value.splice(index, 1);
      }
    };
    
    // 切换类别选择
    const toggleCategory = (categoryKey) => {
      const index = selectedCategories.value.indexOf(categoryKey);
      if (index === -1) {
        selectedCategories.value.push(categoryKey);
      } else {
        selectedCategories.value.splice(index, 1);
      }
    };
    
    // 应用重要性筛选
    const applyImportanceFilter = (filter) => {
      currentFilter.value = filter;
    };
    
    // 重置筛选
    const resetFilters = () => {
      currentFilter.value = 'recommended';
      selectedCategories.value = ['basic', 'symptoms', 'medication', 'allergiesAndContraindications'];
    };
    
    // 应用模板
    const applyTemplate = (template) => {
      if (template === 'common') {
        selectedCategories.value = ['basic', 'symptoms', 'allergiesAndContraindications', 'medication'];
        expandedCategories.value = ['basic', 'symptoms'];
        currentFilter.value = 'recommended';
      } else if (template === 'chronic') {
        selectedCategories.value = ['basic', 'symptoms', 'medicalHistory', 'medication', 'lifestyle'];
        expandedCategories.value = ['basic', 'symptoms', 'medicalHistory'];
        currentFilter.value = 'recommended';
      } else if (template === 'emergency') {
        selectedCategories.value = ['basic', 'symptoms', 'allergiesAndContraindications', 'medication'];
        expandedCategories.value = ['basic', 'symptoms', 'allergiesAndContraindications'];
        currentFilter.value = 'essential';
      }
    };
    
    // 重置表单
    const resetForm = () => {
      // 重置所有字段
      for (const category in patientInfo) {
        for (const field in patientInfo[category]) {
          if (Array.isArray(patientInfo[category][field])) {
            patientInfo[category][field] = [];
          } else if (typeof patientInfo[category][field] === 'object' && patientInfo[category][field] !== null) {
            patientInfo[category][field].value = null;
          } else {
            patientInfo[category][field] = "";
          }
        }
      }
    };
    
    // 保存患者信息
    const savePatientInfo = () => {
      const patientInfoCopy = JSON.parse(JSON.stringify(patientInfo));
      console.log('保存患者信息:', patientInfoCopy);
      
      // 发送更新事件
      emit('update:patientInfo', patientInfoCopy);
      emit('save');
      
      // 保存到本地存储
      localStorage.setItem('patientInfo', JSON.stringify(patientInfoCopy));
      
      // 添加用户反馈
      alert('患者信息已保存，将用于AI分析');
    };
    
    // 组件挂载时，尝试从本地存储加载数据
    onMounted(() => {
      const savedInfo = localStorage.getItem('patientInfo');
      if (savedInfo) {
        try {
          const parsedInfo = JSON.parse(savedInfo);
          
          // 合并保存的数据到当前数据结构
          for (const category in parsedInfo) {
            if (patientInfo[category]) {
              for (const field in parsedInfo[category]) {
                if (field in patientInfo[category]) {
                  patientInfo[category][field] = parsedInfo[category][field];
                }
              }
            }
          }
          
          // 初始化时发送一次更新
          emit('update:patientInfo', JSON.parse(JSON.stringify(patientInfo)));
        } catch (e) {
          console.error('加载保存的患者信息失败', e);
        }
      }
    });
    
    return {
      patientInfo,
      categories,
      filteredCategories,
      showFilterPanel,
      currentFilter,
      selectedCategories,
      expandedCategories,
      isFieldVisible,
      toggleCategoryExpand,
      toggleCategory,
      applyImportanceFilter,
      resetFilters,
      applyTemplate,
      resetForm,
      savePatientInfo
    };
  }
};
</script>

<style scoped>
.patient-info-form {
  display: flex;
  flex-direction: column;
  height: 100%;
  width: 100%; /* 确保表单占满容器宽度 */
  overflow: hidden;
  background-color: white;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-sm);
  box-sizing: border-box; /* 确保padding不会增加宽度 */
}

.form-header {
  padding: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
  background: #f9f9f9;
}

.form-header h2 {
  margin: 0;
  font-size: 1.2em;
  color: var(--text-color);
}

.filter-controls {
  position: relative;
}

.filter-toggle {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 20px;
  cursor: pointer;
  font-size: 0.9em;
  transition: all 0.2s;
}

.filter-toggle:hover {
  background: #f0f0f0;
}

.filter-toggle svg {
  width: 16px;
  height: 16px;
}

.filter-panel {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 10px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  width: 300px;
  z-index: 100;
  overflow: hidden;
  animation: slideDown 0.3s;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.filter-options {
  padding: 15px;
}

.filter-section {
  margin-bottom: 15px;
}

.filter-section:last-child {
  margin-bottom: 0;
}

.filter-section h3 {
  margin: 0 0 10px 0;
  font-size: 0.95em;
  color: #555;
}

.filter-buttons {
  display: flex;
  gap: 8px;
}

.filter-buttons button {
  background: #f0f0f0;
  border: none;
  padding: 6px 12px;
  border-radius: 15px;
  font-size: 0.85em;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-buttons button.active {
  background: #1976D2;
  color: white;
}

.category-checkboxes {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.category-checkbox {
  display: flex;
  align-items: center;
  font-size: 0.9em;
}

.category-checkbox input {
  margin-right: 5px;
}

.template-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.template-buttons button {
  background: #e0e0e0;
  border: none;
  padding: 6px 12px;
  border-radius: 15px;
  font-size: 0.85em;
  cursor: pointer;
  transition: all 0.2s;
}

.template-buttons button:hover {
  background: #d0d0d0;
}

.filter-actions {
  display: flex;
  border-top: 1px solid #eee;
}

.filter-actions button {
  flex: 1;
  padding: 12px;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 0.9em;
  transition: all 0.2s;
}

.filter-actions .reset-button {
  color: #666;
  border-right: 1px solid #eee;
}

.filter-actions .apply-button {
  color: #1976D2;
  font-weight: 500;
}

.filter-actions button:hover {
  background: #f5f5f5;
}

.form-content {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
  width: 100%; /* 确保内容宽度占满父容器 */
  box-sizing: border-box; /* 确保padding不会增加宽度 */
}

.form-category {
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 15px;
  overflow: hidden;
  width: 100%; /* 确保类别卡片占满容器宽度 */
}

.category-header {
  padding: 12px 15px;
  background: #f9f9f9;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: background 0.2s;
}

.category-header:hover {
  background: #f0f0f0;
}

.category-header h3 {
  margin: 0;
  font-size: 1em;
  font-weight: 500;
  color: #333;
}

.category-header svg {
  width: 18px;
  height: 18px;
  transition: transform 0.3s;
}

.category-header svg.expanded {
  transform: rotate(180deg);
}

.category-fields {
  padding: 15px;
  animation: fadeIn 0.3s;
  width: 100%; /* 确保字段容器占满父容器宽度 */
  box-sizing: border-box; /* 确保padding不会增加宽度 */
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.form-field {
  margin-bottom: 15px;
  width: 100%; /* 确保表单字段占满容器宽度 */
}

.form-field:last-child {
  margin-bottom: 0;
}

.form-field label {
  display: block;
  margin-bottom: 6px;
  font-size: 0.9em;
  color: #555;
}

.required-mark {
  color: #F44336;
  margin-left: 3px;
}

input[type="text"],
input[type="number"],
textarea,
select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.95em;
  transition: all 0.3s;
  background: #f9f9f9;
  box-sizing: border-box; /* 确保padding不会增加宽度 */
}

input[type="text"]:focus,
input[type="number"]:focus,
textarea:focus,
select:focus {
  border-color: #1976D2;
  box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
  outline: none;
  background: white;
}

.number-input {
  position: relative;
  width: 100%; /* 确保数字输入框占满容器宽度 */
}

.input-unit {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #777;
  font-size: 0.9em;
}

.number-input input {
  padding-right: 30px;
}

.radio-group,
.checkbox-group {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  width: 100%; /* 确保单选/复选组占满容器宽度 */
}

.radio-option,
.checkbox-option {
  display: flex;
  align-items: center;
  margin-right: 15px;
}

.radio-option label,
.checkbox-option label {
  margin: 0 0 0 5px;
  cursor: pointer;
}

.slider-input {
  display: flex;
  align-items: center;
  gap: 15px;
  width: 100%; /* 确保滑块输入占满容器宽度 */
}

.slider-input input {
  flex: 1;
}

.slider-value {
  width: 30px;
  text-align: center;
  font-weight: 500;
  color: #1976D2;
}

.compound-field {
  display: flex;
  gap: 10px;
  width: 100%; /* 确保复合字段占满容器宽度 */
}

.compound-field input {
  flex: 1;
}

.compound-field select {
  width: 100px;
}

.field-description {
  font-size: 0.8em;
  color: #777;
  margin-top: 5px;
}

.form-actions {
  padding: 15px;
  display: flex;
  gap: 10px;
  background: white;
  border-top: 1px solid #eee;
  width: 100%; /* 确保操作按钮区域占满容器宽度 */
  box-sizing: border-box; /* 确保padding不会增加宽度 */
}

.form-actions button {
  padding: 10px 20px;
  border-radius: 6px;
  border: none;
  font-size: 0.95em;
  cursor: pointer;
  transition: all 0.2s;
}

.reset-button {
  background: #f0f0f0;
  color: #555;
}

.reset-button:hover {
  background: #e0e0e0;
}

.save-button {
  background: #1976D2;
  color: white;
  flex: 1;
}

.save-button:hover {
  background: #1565C0;
}

/* 响应式调整 */
@media (max-width: 768px) {
  .filter-panel {
    width: 280px;
    right: -10px; /* 调整位置，确保在小屏幕上不会超出边界 */
  }
  
  .category-checkboxes {
    grid-template-columns: 1fr;
  }
  
  .radio-group,
  .checkbox-group {
    flex-direction: column;
    gap: 8px;
  }
  
  .radio-option,
  .checkbox-option {
    margin-right: 0;
  }
  
  .compound-field {
    flex-direction: column;
  }
  
  .compound-field select {
    width: 100%;
  }
  
  .form-content {
    padding: 10px; /* 减小内边距，在小屏幕上节省空间 */
  }
  
  .category-fields {
    padding: 10px; /* 减小内边距，在小屏幕上节省空间 */
  }
  
  .form-actions {
    padding: 10px; /* 减小内边距，在小屏幕上节省空间 */
  }
}
</style>
