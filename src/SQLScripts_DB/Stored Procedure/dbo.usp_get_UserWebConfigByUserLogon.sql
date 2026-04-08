USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_UserWebConfigByUserLogon]    Script Date: 01/03/2023 18:56:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER  PROCEDURE [dbo].[usp_get_UserWebConfigByUserLogon]  
@strLogin VARCHAR(50)  
AS  
BEGIN  
 -- SET NOCOUNT ON added to prevent extra result sets from  
 -- interfering with SELECT statements.  
 SET NOCOUNT ON;  
  
SELECT max(HourWidth) as HourWidth  FROM User_Web_Config where Login = @strLogin
END