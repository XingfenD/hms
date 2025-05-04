import {
  createRouter,
  createWebHistory
} from 'vue-router'

import Login from '../views/Login.vue'
import Admin from '../views/admin/Admin.vue'
import ManageUser from '@/views/admin/ManageUser.vue'
import DoctorSchedule from '@/views/admin/DoctorSchedule.vue'
import PatientRegistration from '@/views/admin/PatientRegistration.vue'
import DrugManage from '@/views/admin/DrugManage.vue'
import DoctorManage from '@/views/admin/DoctorManage.vue'
import DbManage from '@/views/admin/DbManage.vue'
import Patient from '@/views/patient/Patient.vue'
import PatientRegister from '@/views/patient/PatientRegister.vue'
import Doctor from '@/views/doctor/Doctor.vue'
import DoctorSchedule_test from '@/views/admin/DoctorSchedule_test.vue'
import Drugadmin from '@/views/drugadmin/Drugadmin.vue'
import AiSystemApp from '@/ai_system/App.vue'
import Admin_test from '@/views/admin/Admin_test.vue'

const router = createRouter({
  history: createWebHistory(
    import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login'
    },
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    {
      path: '/admin',
      // component: Admin
      component: Admin_test
    },
    {
      path: '/admin/user-manage',
      component: ManageUser,
      meta: {
        requiresAuth: true,
        role: 'admin'
      }
    },
    {
      path: '/admin/doctor-schedule',
      component: DoctorSchedule,
      meta: {
        requiresAuth: true,
        role: 'admin'
      }
    },
    {
      path: '/admin/patient-registration',
      component: PatientRegistration,
      meta: {
        requiresAuth: true,
        role: 'admin'
      }
    },
    {
      path: "/admin/drug-manage",
      component: DrugManage,
      meta: {
        requiresAuth: true,
        role: 'admin'
      }
    },
    {
      path: "/admin/doctor-manage",
      component: DoctorManage,
      meta: {
        requiresAuth: true,
        role: 'admin'
      }
    },
    {
      path: "/admin/db-manage",
      component: DbManage,
      meta: {
        requiresAuth: true,
        role: 'admin'
      }
    },
    {
      path: '/patient',
      component: Patient,
      children: [
        {
          path: 'register',
          component: PatientRegister,
          meta: { menuKey: 'register' }
        },
        {
          path: 'appointments',
          component: () => import('../views/patient/PatientAppointments.vue'),
          meta: { menuKey: 'appointments' }
        },
        {
          path: 'payments',
          component: () => import('../views/patient/PatientPayments.vue'),
          meta: { menuKey: 'payments' }
        },
        {
          path: 'records',
          component: () => import('../views/patient/PatientRecords.vue'),
          meta: { menuKey: 'records' }
        },
        {
          path: 'profile',
          component: () => import('../views/patient/Profile.vue'),
        }
      ]
    },
    {
      path: "/doctor",
      component: Doctor,
      children: [
        {
          path: 'call',
          name: 'DoctorCall',
          component: () => import('@/views/doctor/CallPatient.vue'),
          meta: { menuKey: 'call' }
        },
        {
          path: 'prescribe',
          name: 'DoctorPrescribe',
          component: () => import('@/views/doctor/Prescribe.vue'),
          meta: { menuKey: 'prescribe' }
        },
        {
          path: 'records',
          name: 'DoctorRecords',
          component: () => import('@/views/doctor/MedicalRecords.vue'),
          meta: { menuKey: 'records' }
        },
        // {
        //   path: 'prescriptions',
        //   name: 'DoctorPrescriptions',
        //   component: () => import('@/views/doctor/PrescriptionManagement.vue'),
        //   meta: { menuKey: 'prescriptions' }
        // }
        {
          path: 'profile',
          name: 'DoctorProfile',
          component: () => import('@/views/doctor/Profile.vue'),
        },
        {
          path: 'checkup',
          name: 'DoctorCheckup',
          component: () => import('@/views/doctor/CheckUp.vue'),
        },
        {
          path: 'checkupRecords',
          name: 'DoctorcheckupRecords',
          component: () => import('@/views/doctor/CheckupRecords.vue')
        }
      ]
    },
    {
      path: '/drugadmin',
      component: Drugadmin,
    },
    {
      path: '/ai-system',
      name: 'AiSystem',
      component: AiSystemApp
    },
  ],
})

export default router