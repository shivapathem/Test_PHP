USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_StaffDetailsDisplayNameByStaffID]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE  [dbo].[usp_get_StaffDetailsDisplayNameByStaffID]
	-- Add the parameters for the stored procedure here
	@staffID INT = 0 
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
   -- interfering with SELECT statements.
    SET NOCOUNT ON;

 

    -- Insert statements for procedure here
    SELECT sd.Forename,sd.PreferredForename,sd.NetLogin,sd.Surname,sd.Title,sd.StaffNumber,sp.DisplayName
     from StaffDetails sd  left Join ScheduledPeople sp on sp.StaffDetailsID = sd.StaffID where sd.StaffID =@staffID 
END

'

EXEC dbo.sp_executesql @strSQL

GO






