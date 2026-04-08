USE [ALLOCATE7]
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_UpdateUserWebConfigHourWidth]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'  PROCEDURE [dbo].[usp_UpdateUserWebConfigHourWidth]
@strLogin VARCHAR(50) , 
@intZoomAmount INT,
@HourWidth INT = 75

AS
BEGIN
	SET NOCOUNT ON;
    IF EXISTS (SELECT HourWidth FROM User_Web_Config  
		WHERE   (Login = @strLogin))
		
        Update User_Web_Config SET HourWidth = (SELECT HourWidth FROM User_Web_Config  
		WHERE   (Login = @strLogin))+@intZoomAmount
		WHERE  (Login = @strLogin) 
	ELSE 
		INSERT INTO User_Web_Config (HourWidth, Login)
		VALUES (@HourWidth, @strLogin)
END
'
EXEC dbo.sp_executesql @strSQL

GO
