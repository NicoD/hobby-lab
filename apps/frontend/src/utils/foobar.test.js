import { FooBar } from './foobar.js'

describe('FooBar', () => {
  it('buzz returns true', () => {
    const fooBar = new FooBar()
    expect(fooBar.buzz()).toBe(true)
  })
})
