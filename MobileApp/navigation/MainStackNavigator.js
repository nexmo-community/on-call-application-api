import * as React from 'react'
import { NavigationContainer } from '@react-navigation/native'
import { createStackNavigator } from '@react-navigation/stack'

import Login from '../components/LoginScreen'

const Stack = createStackNavigator()

function MainStackNavigator() {
  return (
    <NavigationContainer>
      <Stack.Navigator initialRouteName='Login'>
        <Stack.Screen 
          name='Login' 
          component={Login} 
          options={{ title: 'Login Screen', headerShown: false }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  )
}

export default MainStackNavigator;