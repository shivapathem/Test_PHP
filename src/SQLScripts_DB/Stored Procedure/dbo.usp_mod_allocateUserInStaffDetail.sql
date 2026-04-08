USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_mod_allocateUserInStaffDetail]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_mod_allocateUserInStaffDetail]
	@empNumber VARCHAR(80),
	@surname VARCHAR(80),
	@firstName VARCHAR(80),
	@netLogin VARCHAR(80),
	@email VARCHAR(80)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	IF NOT EXISTS (SELECT 1 FROM StaffDetails (NOLOCK) where NetLogin = @netlogin)
		 BEGIN	
			INSERT INTO [dbo].[StaffDetails]([EmpNumber],[StaffNumber],[Surname],[Forename],[NetLogin],[InternalEmail],[CreatedDate],[IsScheduledPerson])
			VALUES (@empNumber,@empNumber,@surname,@firstName,@netlogin,@email,getdate(),''0'')
		 END
END
'

EXEC dbo.sp_executesql @strSQL

GO

