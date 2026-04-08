USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_UpdateNONBBCEmailByStaffID]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_UpdateNONBBCEmailByStaffID]
@StaffID INT,
@ExternalEmail VARCHAR(132),
@AltTelephone VARCHAR(40)
AS
BEGIN
	SET NOCOUNT ON;
   
	UPDATE       StaffDetails
             SET          ExternalEmail = @ExternalEmail,AltTelephone=@AltTelephone
             WHERE       (StaffID = @StaffID)
	

END
'

EXEC dbo.sp_executesql @strSQL

GO