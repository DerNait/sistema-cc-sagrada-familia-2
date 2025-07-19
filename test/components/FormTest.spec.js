import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import Form from '../../resources/js/components/Crud/Form.vue'

describe('Form.vue (Users)', () => {
  beforeEach(() => {
    const meta = document.createElement('meta')
    meta.name = 'csrf-token'
    meta.content = 'test-token'
    document.head.appendChild(meta)
  })

  const columns = {
    name: { field: 'name', label: 'Nombre', type: 'text', editable: true },
    apellido: { field: 'apellido', label: 'Apellido', type: 'text', editable: true },
    email: { field: 'email', label: 'Correo', type: 'text', editable: true },
    rol_id: {
      field: 'rol_id',
      label: 'Rol',
      type: 'relation',
      editable: true,
      options: {
        1: 'Administrador',
        2: 'Editor',
        3: 'Visitante'
      }
    },
    fecha_nacimiento: {
      field: 'fecha_nacimiento',
      label: 'Fecha nacimiento',
      type: 'date',
      editable: true
    }
  }

  const item = {
    name: 'Kevin',
    apellido: 'Villagrán',
    email: 'kevin@example.com',
    rol_id: 2,
    fecha_nacimiento: '1999-12-31'
  }

  it('renderiza campos correctamente del formulario de usuario', () => {
    const wrapper = mount(Form, {
      props: { columns, item, action: '/usuarios' }
    })

    expect(wrapper.find('input[name="name"]').element.value).toBe('Kevin')
    expect(wrapper.find('input[name="apellido"]').element.value).toBe('Villagrán')
    expect(wrapper.find('input[name="email"]').element.value).toBe('kevin@example.com')
    expect(wrapper.find('select[name="rol_id"]').element.value).toBe('2')
    expect(wrapper.find('input[name="fecha_nacimiento"]').element.value).toBe('1999-12-31')
  })

  it('renderiza título correctamente según el pathname', () => {
    Object.defineProperty(window, 'location', {
      value: { pathname: '/usuarios/create' },
      writable: true
    })

    const wrapper = mount(Form, {
      props: { columns, action: '/usuarios' }
    })

    expect(wrapper.find('h4').text()).toContain('Nuevo Usuarios')
  })
})
