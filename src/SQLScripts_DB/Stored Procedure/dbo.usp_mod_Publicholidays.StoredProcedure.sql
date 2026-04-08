USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_mod_Publicholidays]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_mod_Publicholidays]
@id       int,
@action VARCHAR(20), 
@calenderyear VARCHAR(50), 
@description VARCHAR(500),
@holidaydate date,
@week varchar(50),
@isactive bit,
@createdby       INT,
@modifedby int,
@status                                                         VARCHAR(20) OUTPUT,
@returnstring                 VARCHAR(1000) OUTPUT

AS
BEGIN
              -- SET NOCOUNT ON added to prevent extra result sets from
              -- interfering with SELECT statements.
              SET NOCOUNT ON;

              DECLARE @err int
              DECLARE @rows int
              DECLARE @RowNum int
              
              DECLARE @newid INT
              DECLARE @historytype int
              DECLARE @activestring varchar(15)
              

              SET @activestring = ''activated''
              

              SET @status = ''success''
              SET @returnstring = ''Holiday record has been created successfully.''

BEGIN TRANSACTION
              if @action = ''insert''
              Begin
                             INSERT INTO PublicHolidays (CalenderYear, HolidayDate, [Week],[Description],IsActive,CreatedBy,CreatedDate,IsDeleted)
                             VALUES (@calenderyear,@holidaydate, @week,@description,@isactive,@createdby,getdate(),0);
                             -- get last insert data
                                           SET @newid = Scope_Identity();
                             

                             --check error
        SELECT @err = @@ERROR, @rows = @@ROWCOUNT
                                                                        IF @err <> 0 
                                                                                      BEGIN
                                                                                                     ROLLBACK TRANSACTION
                                                                                                     SET @status = ''error''
                                                                                                     SET @returnstring = ''Error saving the holiday data.''
                                                                                                     SELECT @status strstatus , @returnstring strreturnstring;
                                                                                                     RETURN ;
                                                                                      END
                                                                        IF @rows = 0 
                                                                                      BEGIN
                                                                                                     ROLLBACK TRANSACTION
                                                                                                     SET @status = ''error''
                                                                                                     SET @returnstring = ''Error saving the holiday Details.''
                                                                                                     SELECT @status strstatus , @returnstring strreturnstring;
                                                                                                     RETURN ;
                                                                                      END
                                           
              End

              Else
                             if @action = ''update''
              Begin
                             UPDATE PublicHolidays  SET [Description] = @description,
                                                                                                                                  ModifiedBy        = @createdby,
                                                                                                                                  ModifiedDate = getdate()
                                                                                                                   WHERE PublicHolidayId       =   @id;

                             SELECT @err = @@ERROR, @rows = @@ROWCOUNT
                             IF @err <> 0 
                             BEGIN
                                           ROLLBACK TRANSACTION
                                           SET @status = ''error''
                                           SET @returnstring = ''Error updating the holiday data.''
                                           SELECT @status strstatus , @returnstring strreturnstring;
                                           RETURN ;
                             END
                             IF @rows = 0 
                             BEGIN
                                           ROLLBACK TRANSACTION
                                           SET @status = ''error''
                                           SET @returnstring = ''Error updating the holiday Details.''
                                           SELECT @status strstatus , @returnstring strreturnstring;
                                           RETURN ;
                             END
                                           SET @returnstring = ''Holiday record has been updated successfully.''
              End
                             
                             --SELECT @status strstatus , @returnstring strreturnstring;
                             END
              SELECT @status strstatus , @returnstring strreturnstring;             
              COMMIT TRANSACTION
'

EXEC dbo.sp_executesql @strSQL

GO